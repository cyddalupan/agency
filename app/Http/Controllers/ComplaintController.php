<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\User;
use App\Services\ComplaintWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * QMS-14 (card 6afa0dab2a673e08b88cdd67): complaint intake, register and
 * resolution screens.
 *
 * The UI over the QMS-13 spine. Intake takes a complaint and derives its SLA
 * deadlines from the agency's own response windows; the register is a status
 * tabbed list that puts overdue work where it cannot be missed; the detail
 * screen is where the complaint actually gets acknowledged and resolved.
 *
 * Lifecycle rules live in ComplaintWorkflow, not here, so a rule cannot be
 * bypassed by a future screen that forgets to check it. This controller only
 * resolves which action was asked for and translates the outcome into a
 * redirect or a form error.
 *
 * Roles mirror the risk register and document control: read for super_admin /
 * admin / qms_manager / internal_auditor, manage (create and move) for the
 * first three. The Internal Auditor reads complaints; it does not get to
 * acknowledge or resolve them, because it is meant to audit the handling.
 */
class ComplaintController extends Controller
{
    /** Roles allowed to log and move complaints. */
    private const MANAGE_ROLES = ['super_admin', 'admin', 'qms_manager'];

    /** Roles allowed to read the register and the detail screen. */
    private const READ_ROLES = ['super_admin', 'admin', 'qms_manager', 'internal_auditor'];

    /** The status tabs across the top of the register. */
    private const TABS = [
        'all'           => 'All',
        Complaint::STATUS_OPEN          => 'Open',
        Complaint::STATUS_ACKNOWLEDGED  => 'Acknowledged',
        Complaint::STATUS_INVESTIGATING => 'Investigating',
        Complaint::STATUS_RESOLVED      => 'Resolved',
        Complaint::STATUS_CLOSED        => 'Closed',
        'overdue'       => 'Overdue',
    ];

    public function __construct(private readonly ComplaintWorkflow $workflow)
    {
    }

    /**
     * The register: status tabs, overdue highlighting, search.
     *
     * Overdue is a view of the same table rather than a stored status: a
     * complaint becomes overdue because time passed, not because anyone
     * changed it, and writing that to a column would need a cron to stay
     * truthful.
     */
    public function index(Request $request): View
    {
        $this->authoriseRead($request);

        $filters = $request->validate([
            'status' => ['nullable', 'string'],
            'q'      => ['nullable', 'string', 'max:120'],
        ]);

        $tab = $filters['status'] ?? 'all';
        if (! array_key_exists($tab, self::TABS)) {
            $tab = 'all';
        }

        $query = Complaint::query()
            ->with(['assignee'])
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('ref_no', 'like', '%' . $term . '%')
                          ->orWhere('complainant_name', 'like', '%' . $term . '%')
                          ->orWhere('description', 'like', '%' . $term . '%');
                });
            });

        if ($tab === 'overdue') {
            // Late on either clock, and still live. Mirrors the model's
            // is_ack_overdue / is_resolve_overdue accessors exactly so the tab
            // and the row badge can never disagree.
            $today = now()->startOfDay();

            $query->whereNotIn('status', Complaint::CLOSED_STATUSES)
                  ->where(function ($inner) use ($today) {
                      $inner->where(function ($ack) use ($today) {
                          $ack->whereNull('acknowledged_at')
                              ->whereNotNull('target_ack_date')
                              ->whereDate('target_ack_date', '<', $today);
                      })->orWhere(function ($resolve) use ($today) {
                          $resolve->whereNull('resolved_at')
                                  ->whereNotNull('target_resolve_date')
                                  ->whereDate('target_resolve_date', '<', $today);
                      });
                  });
        } elseif ($tab !== 'all') {
            $query->where('status', $tab);
        }

        $complaints = $query
            // Live work first, then the terminal statuses. A portable CASE
            // rather than MySQL's FIELD(): the test suite runs on SQLite, and
            // an ordering that only works in production is not worth having.
            ->orderByRaw('CASE status ' . collect(Complaint::STATUSES)
                ->map(fn ($status, $index) => 'WHEN ? THEN ' . $index)
                ->implode(' ') . ' ELSE 99 END', Complaint::STATUSES)
            ->orderBy('target_resolve_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        // Counts for every tab, computed from the whole register rather than
        // the filtered page, so a tab never claims zero because of a search.
        $base = Complaint::query()->when($filters['q'] ?? null, function ($q, $term) {
            $q->where(function ($inner) use ($term) {
                $inner->where('ref_no', 'like', '%' . $term . '%')
                      ->orWhere('complainant_name', 'like', '%' . $term . '%')
                      ->orWhere('description', 'like', '%' . $term . '%');
            });
        });

        $counts = ['all' => (clone $base)->count(), 'overdue' => $this->overdueCount(clone $base)];
        foreach (Complaint::STATUSES as $status) {
            $counts[$status] = (clone $base)->where('status', $status)->count();
        }

        return view('complaints.index', [
            'complaints' => $complaints,
            'filters'    => $filters,
            'tab'        => $tab,
            'tabs'       => self::TABS,
            'counts'     => $counts,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorise($request);

        return view('complaints.create', [
            'nextRef'  => $this->suggestNextRef(),
            'types'    => Complaint::COMPLAINANT_TYPES,
            'sla'      => Complaint::slaFor($this->currentAgency()),
            'assignees' => $this->assigneeOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorise($request);

        $agencyId = $this->resolveAgencyId();
        abort_if($agencyId === null, 403, 'No agency context.');

        $validated = $request->validate([
            'ref_no'           => [
                'required', 'string', 'max:64',
                Rule::unique('complaints', 'ref_no')->where('agency_id', $agencyId),
            ],
            'complainant_name' => ['required', 'string', 'max:255'],
            'complainant_type' => ['required', Rule::in(array_keys(Complaint::COMPLAINANT_TYPES))],
            'category'         => ['nullable', 'string', 'max:255'],
            'description'      => ['required', 'string', 'max:5000'],
            'date_received'    => ['required', 'date'],
            'assigned_to'      => ['nullable', 'integer', 'exists:users,id'],
        ], [
            'ref_no.unique' => 'That reference is already in use for this agency.',
        ]);

        $complaint = Complaint::create([
            ...$validated,
            'agency_id'    => $agencyId,
            'status'       => Complaint::STATUS_OPEN,
            'created_by'   => $request->user()?->id,
            // Deliberately not passing target dates: the model derives both
            // from the agency SLA on save, so intake cannot disagree with the
            // configured window.
        ]);

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', "{$complaint->ref_no} received. Acknowledge by "
                . $complaint->target_ack_date->format('d M Y')
                . ', resolve by ' . $complaint->target_resolve_date->format('d M Y') . '.');
    }

    public function show(Request $request, Complaint $complaint): View
    {
        $this->authoriseRead($request);

        $complaint->load(['assignee', 'creator', 'applicant']);

        return view('complaints.show', [
            'complaint' => $complaint,
            'canManage' => $this->canManage($request),
        ]);
    }

    /** Acknowledge: record that the complainant has been told we have it. */
    public function acknowledge(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->authorise($request);

        return $this->run($complaint, fn () => $this->workflow->acknowledge($complaint),
            "{$complaint->ref_no} acknowledged.");
    }

    /** Move to investigating. */
    public function investigate(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->authorise($request);

        return $this->run($complaint, fn () => $this->workflow->investigate($complaint),
            "{$complaint->ref_no} marked as investigating.");
    }

    /** Resolve: the outcome must be written down. */
    public function resolve(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->authorise($request);

        $validated = $request->validate([
            'resolution' => ['required', 'string', 'max:5000'],
        ], [
            'resolution.required' => 'Write what was done before resolving: an auditor has to be able to test the outcome.',
        ]);

        return $this->run($complaint, fn () => $this->workflow->resolve($complaint, $validated['resolution']),
            "{$complaint->ref_no} resolved.");
    }

    /** Close a resolved complaint. */
    public function close(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->authorise($request);

        $validated = $request->validate([
            'closure_notes' => ['required', 'string', 'max:5000'],
        ], [
            'closure_notes.required' => 'Closure notes are required to close a complaint.',
        ]);

        return $this->run($complaint, fn () => $this->workflow->close($complaint, $validated['closure_notes']),
            "{$complaint->ref_no} closed.");
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Run a lifecycle action and turn a workflow refusal into a form error
     * instead of a 500. The detail screen is a page people are meant to use,
     * so "you cannot close an open complaint" belongs next to the button.
     */
    private function run(Complaint $complaint, callable $action, string $message): RedirectResponse
    {
        try {
            $action();
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->route('complaints.show', $complaint)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', $message);
    }

    /** Overdue count over whatever the current search narrowed the register to. */
    private function overdueCount($query): int
    {
        $today = now()->startOfDay();

        return $query->whereNotIn('status', Complaint::CLOSED_STATUSES)
            ->where(function ($inner) use ($today) {
                $inner->where(function ($ack) use ($today) {
                    $ack->whereNull('acknowledged_at')
                        ->whereNotNull('target_ack_date')
                        ->whereDate('target_ack_date', '<', $today);
                })->orWhere(function ($resolve) use ($today) {
                    $resolve->whereNull('resolved_at')
                            ->whereNotNull('target_resolve_date')
                            ->whereDate('target_resolve_date', '<', $today);
                });
            })
            ->count();
    }

    /** Next free reference in the CMP-nnn series, for convenience. */
    private function suggestNextRef(): string
    {
        $last = Complaint::withTrashed()
            ->where('ref_no', 'like', 'CMP-%')
            ->pluck('ref_no')
            ->map(fn ($ref) => (int) preg_replace('/\D/', '', (string) $ref))
            ->max() ?? 0;

        return 'CMP-' . str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
    }

    /** Assignee dropdown: users of this agency. */
    private function assigneeOptions(): array
    {
        return User::query()
            ->orderBy('surname')
            ->get(['id', 'name', 'middle_name', 'surname'])
            ->mapWithKeys(fn (User $u) => [
                $u->id => trim(implode(', ', array_filter([$u->surname, $u->name]))) ?: ('User #' . $u->id),
            ])
            ->all();
    }

    private function canManage(Request $request): bool
    {
        return in_array((string) $request->user()?->user_type, self::MANAGE_ROLES, true);
    }

    /**
     * The agency whose SLA drives the intake preview.
     *
     * `tenant_agency()` is set in the request lifecycle but not by a test's
     * `actingAs()`, so the authenticated user's agency is the fallback rather
     * than the only source.
     */
    private function currentAgency(): ?\App\Models\Agency
    {
        $agency = tenant_agency();
        if ($agency !== null) {
            return $agency;
        }

        $agencyId = auth()->user()?->agency_id;

        return $agencyId ? \App\Models\Agency::find($agencyId) : null;
    }

    private function authorise(Request $request): void
    {
        abort_unless(
            $this->canManage($request),
            403,
            'You are not allowed to manage complaints.'
        );
    }

    private function authoriseRead(Request $request): void
    {
        abort_unless(
            in_array((string) $request->user()?->user_type, self::READ_ROLES, true),
            403,
            'You are not allowed to view complaints.'
        );
    }
}
