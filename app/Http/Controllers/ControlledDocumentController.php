<?php

namespace App\Http\Controllers;

use App\Models\ControlledDocument;
use App\Models\ControlledDocumentVersion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * QMS-08 (card 6afa0da5dd70c2a22cda903e): document-control create + list.
 *
 * Spec §26 document control. This is the first *screen* over the QMS-06/QMS-07
 * spine: a register of controlled documents, and a form to add one.
 *
 * Gated to super_admin / admin / qms_manager. QMS-09 adds the `qms_manager`
 * role properly; it is listed here now so this screen does not have to be
 * revisited when that card lands.
 */
class ControlledDocumentController extends Controller
{
    /** Roles allowed to *manage* (read + write) controlled documents. */
    private const ALLOWED_ROLES = ['super_admin', 'admin', 'qms_manager'];

    /**
     * Roles allowed to *read* the register. QMS-09: the Internal Auditor may
     * see documents (it audits against them) but must not create or change
     * them — spec §19 read-only rule.
     */
    private const READ_ROLES = ['super_admin', 'admin', 'qms_manager', 'internal_auditor'];

    /** The register list: code, title, version, status, next review, owner. */
    public function index(Request $request): View
    {
        $this->authoriseRead($request);

        $filters = $request->validate([
            'status' => ['nullable', 'string', 'max:32'],
            'q'      => ['nullable', 'string', 'max:120'],
        ]);

        $documents = ControlledDocument::query()
            ->with(['currentVersion', 'processOwner'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('code', 'like', '%' . $term . '%')
                          ->orWhere('title', 'like', '%' . $term . '%');
                });
            })
            ->orderBy('code')
            ->paginate(25)
            ->withQueryString();

        return view('controlled_documents.index', [
            'documents' => $documents,
            'filters'   => $filters,
            'statuses'  => ControlledDocument::STATUS_LABELS,
        ]);
    }

    /** The create form. */
    public function create(Request $request): View
    {
        $this->authorise($request);

        return view('controlled_documents.create', [
            'owners'      => $this->ownerOptions(),
            'types'       => ControlledDocument::DOCUMENT_TYPES,
            'levels'      => ControlledDocument::CONFIDENTIALITY_LEVELS,
            'frequencies' => ControlledDocument::REVIEW_FREQUENCIES,
        ]);
    }

    /**
     * Store a document, and optionally an initial version with a file.
     *
     * The two are written in one transaction: a document that silently exists
     * without the version the user just uploaded would be worse than a failed
     * save.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorise($request);

        $validated = $request->validate([
            'code'            => ['required', 'string', 'max:64'],
            'title'           => ['required', 'string', 'max:255'],
            'document_type'   => ['nullable', 'in:' . implode(',', array_keys(ControlledDocument::DOCUMENT_TYPES))],
            'department'      => ['nullable', 'string', 'max:255'],
            'process_owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'confidentiality' => ['nullable', 'in:' . implode(',', array_keys(ControlledDocument::CONFIDENTIALITY_LEVELS))],
            'review_frequency' => ['nullable', 'in:' . implode(',', array_keys(ControlledDocument::REVIEW_FREQUENCIES))],
            'next_review_date' => ['nullable', 'date'],
            'file'            => ['nullable', 'file', 'max:20480'], // 20 MB
        ]);

        // Inherited from the base Controller: auth user's agency, else tenant.
        $agencyId = $this->resolveAgencyId();

        abort_if($agencyId === null, 403, 'No agency context.');

        // Code is unique per agency. Check up-front so the user gets a field
        // error rather than a raw constraint violation.
        $exists = ControlledDocument::withTrashed()
            ->where('agency_id', $agencyId)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['code' => 'That document code is already in use for this agency.'])
                ->withInput();
        }

        $document = DB::transaction(function () use ($request, $validated, $agencyId) {
            $document = ControlledDocument::create([
                'agency_id'        => $agencyId,
                'code'             => $validated['code'],
                'title'            => $validated['title'],
                'document_type'    => $validated['document_type'] ?? null,
                'department'       => $validated['department'] ?? null,
                'process_owner_id' => $validated['process_owner_id'] ?? null,
                'confidentiality'  => $validated['confidentiality'] ?? 'internal',
                'status'           => ControlledDocument::STATUS_DRAFT,
                'next_review_date' => $validated['next_review_date'] ?? null,
                'review_frequency' => $validated['review_frequency'] ?? null,
                'created_by'       => $request->user()?->id,
            ]);

            // An uploaded file implies a first version exists.
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('controlled-documents', 'public');

                $version = ControlledDocumentVersion::create([
                    'controlled_document_id' => $document->id,
                    'agency_id'              => $agencyId,
                    'version_no'             => ControlledDocumentVersion::nextVersionNo(null),
                    'file_path'              => $path,
                    'file_name'              => $file->getClientOriginalName(),
                    'prepared_by'            => $request->user()?->id,
                    'status'                 => ControlledDocumentVersion::STATUS_DRAFT,
                ]);

                // A draft version is the current pointer until review happens.
                $document->update(['current_version_id' => $version->id]);
            }

            return $document;
        });

        return redirect()
            ->route('controlled_documents.index')
            ->with('success', "Document {$document->code} created.");
    }

    /**
     * Only super_admin, admin and qms_manager may use document control.
     * Everyone else gets 403 — not a redirect — so a probe sees the refusal.
     */
    private function authorise(Request $request): void
    {
        abort_unless(
            in_array((string) $request->user()?->user_type, self::ALLOWED_ROLES, true),
            403,
            'You are not allowed to manage controlled documents.'
        );
    }

    /** Read-only access (adds Internal Auditor). */
    private function authoriseRead(Request $request): void
    {
        abort_unless(
            in_array((string) $request->user()?->user_type, self::READ_ROLES, true),
            403,
            'You are not allowed to view controlled documents.'
        );
    }

    /** Process-owner dropdown: users of this agency. */
    private function ownerOptions(): array
    {
        return User::query()
            ->orderBy('surname')
            ->get(['id', 'name', 'middle_name', 'surname'])
            ->mapWithKeys(fn (User $u) => [
                $u->id => trim(implode(', ', array_filter([$u->surname, $u->name]))) ?: ('User #' . $u->id),
            ])
            ->all();
    }
}
