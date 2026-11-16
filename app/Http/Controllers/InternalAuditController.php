<?php

namespace App\Http\Controllers;

use App\Models\AuditFinding;
use App\Models\InternalAudit;
use App\Models\User;
use App\Services\AuditFindingToNonConformity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * QMS-18 (card 6afa0dafe7637ea026605c1c): internal audit screens.
 *
 * The UI over QMS-17's tables: the programme list, the planning form, and the
 * detail screen where an audit is actually conducted — findings recorded, and
 * where one of them is turned into a non-conformity.
 *
 * Two role tiers, and they are not the same split as the rest of the QMS:
 * planning an audit is a management act (who audits what, when), so it is
 * admin/qms_manager only. Recording findings is the auditor's own work, so
 * `internal_auditor` can do that on an audit that already exists. Raising the
 * NC from a finding follows the finding, not the planner.
 */
class InternalAuditController extends Controller
{
    /** Roles allowed to plan, edit and close audits. */
    private const PLAN_ROLES = ['super_admin', 'admin', 'qms_manager'];

    /** Roles allowed to read audits and record findings against them. */
    private const CONDUCT_ROLES = ['super_admin', 'admin', 'qms_manager', 'internal_auditor'];

    public function __construct(private readonly AuditFindingToNonConformity $spawner)
    {
    }

    public function index(Request $request): View
    {
        $this->authoriseConduct($request);

        $filters = $request->validate([
            'q'      => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(InternalAudit::STATUSES)],
        ]);

        $query = InternalAudit::query()
            ->withCount('findings')
            ->with('leadAuditor');

        if (! empty($filters['q'])) {
            $term = $filters['q'];
            $query->where(function ($inner) use ($term) {
                $inner->where('ref_no', 'like', "%{$term}%")
                    ->orWhere('title', 'like', "%{$term}%")
                    ->orWhere('scope', 'like', "%{$term}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $audits = $query
            ->orderByDesc('scheduled_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $counts = [
            'all'         => InternalAudit::count(),
            'planned'     => InternalAudit::where('status', InternalAudit::STATUS_PLANNED)->count(),
            'in_progress' => InternalAudit::where('status', InternalAudit::STATUS_IN_PROGRESS)->count(),
            'completed'   => InternalAudit::where('status', InternalAudit::STATUS_COMPLETED)->count(),
            'closed'      => InternalAudit::where('status', InternalAudit::STATUS_CLOSED)->count(),
        ];

        return view('internal_audits.index', [
            'audits'     => $audits,
            'filters'    => $filters,
            'counts'     => $counts,
            'statuses'   => InternalAudit::STATUS_LABELS,
            'canPlan'    => $this->canPlan($request),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorisePlan($request);

        return view('internal_audits.create', [
            'suggestedRef' => InternalAudit::suggestNextRef(),
            'auditors'     => $this->auditorOptions(),
            'statuses'     => InternalAudit::STATUS_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorisePlan($request);

        $agencyId = $request->user()->agency_id;

        $validated = $request->validate([
            'ref_no'          => [
                'required', 'string', 'max:40',
                Rule::unique('internal_audits', 'ref_no')->where('agency_id', $agencyId),
            ],
            'title'           => ['required', 'string', 'max:255'],
            'scope'           => ['nullable', 'string', 'max:5000'],
            'criteria'        => ['nullable', 'string', 'max:5000'],
            'scheduled_date'  => ['nullable', 'date'],
            'conducted_date'  => ['nullable', 'date'],
            'lead_auditor_id' => ['nullable', 'integer', 'exists:users,id'],
            'status'          => ['required', Rule::in(InternalAudit::STATUSES)],
            'summary'         => ['nullable', 'string', 'max:5000'],
        ], [
            'ref_no.unique' => 'That reference is already in use for this agency.',
        ]);

        $audit = InternalAudit::create(array_merge($validated, [
            'agency_id'  => $agencyId,
            'created_by' => $request->user()->id,
        ]));

        return redirect()
            ->route('internal_audits.show', $audit)
            ->with('success', "{$audit->ref_no} planned.");
    }

    public function show(Request $request, InternalAudit $internalAudit): View
    {
        $this->authoriseConduct($request);

        $internalAudit->load([
            'leadAuditor',
            'findings' => fn ($q) => $q->orderBy('finding_no')->with('nonConformity'),
        ]);

        return view('internal_audits.show', [
            'audit'           => $internalAudit,
            'findings'        => $internalAudit->findings,
            'nextFindingNo'   => AuditFinding::suggestNextNo($internalAudit->id),
            'findingTypes'    => AuditFinding::TYPES,
            'statuses'        => InternalAudit::STATUS_LABELS,
            'canPlan'         => $this->canPlan($request),
            'canConduct'      => $this->canConduct($request),
        ]);
    }

    public function update(Request $request, InternalAudit $internalAudit): RedirectResponse
    {
        $this->authorisePlan($request);

        $validated = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'scope'           => ['nullable', 'string', 'max:5000'],
            'criteria'        => ['nullable', 'string', 'max:5000'],
            'scheduled_date'  => ['nullable', 'date'],
            'conducted_date'  => ['nullable', 'date'],
            'lead_auditor_id' => ['nullable', 'integer', 'exists:users,id'],
            'status'          => ['required', Rule::in(InternalAudit::STATUSES)],
            'summary'         => ['nullable', 'string', 'max:5000'],
        ]);

        $internalAudit->update($validated);

        return redirect()
            ->route('internal_audits.show', $internalAudit)
            ->with('success', "{$internalAudit->ref_no} updated.");
    }

    /**
     * Record a finding against an audit. This is the auditor's own work, so it
     * is gated on conduct rather than plan.
     */
    public function storeFinding(Request $request, InternalAudit $internalAudit): RedirectResponse
    {
        $this->authoriseConduct($request);

        if ($internalAudit->isClosed()) {
            return back()->with('error', 'A closed audit cannot take new findings.');
        }

        $validated = $request->validate([
            'type'             => ['required', Rule::in(array_keys(AuditFinding::TYPES))],
            'clause_reference' => ['nullable', 'string', 'max:255'],
            'description'      => ['required', 'string', 'max:5000'],
            'evidence'         => ['nullable', 'string', 'max:5000'],
        ]);

        $finding = AuditFinding::create(array_merge($validated, [
            'agency_id'         => $internalAudit->agency_id,
            'internal_audit_id' => $internalAudit->id,
            'finding_no'        => AuditFinding::suggestNextNo($internalAudit->id),
            'created_by'        => $request->user()->id,
        ]));

        // Recording a finding on a planned audit is what conducting means, so
        // the status moves itself rather than making the auditor remember.
        if ($internalAudit->status === InternalAudit::STATUS_PLANNED) {
            $internalAudit->update([
                'status'         => InternalAudit::STATUS_IN_PROGRESS,
                'conducted_date' => $internalAudit->conducted_date ?? now()->toDateString(),
            ]);
        }

        return redirect()
            ->route('internal_audits.show', $internalAudit)
            ->with('success', "Finding {$finding->finding_no} recorded.");
    }

    public function destroyFinding(Request $request, InternalAudit $internalAudit, AuditFinding $finding): RedirectResponse
    {
        $this->authoriseConduct($request);

        abort_unless($finding->internal_audit_id === $internalAudit->id, 404);

        if ($finding->hasNonConformity()) {
            return back()->with(
                'error',
                "{$finding->finding_no} already raised {$finding->nonConformity->ref_no}, so it was kept as evidence."
            );
        }

        $finding->delete();

        return redirect()
            ->route('internal_audits.show', $internalAudit)
            ->with('success', "Finding {$finding->finding_no} removed.");
    }

    /**
     * The card's "one click": a finding becomes a non-conformity. The rule
     * itself lives in the service so the screens cannot bypass it.
     */
    public function raiseNonConformity(Request $request, InternalAudit $internalAudit, AuditFinding $finding): RedirectResponse
    {
        $this->authoriseConduct($request);

        abort_unless($finding->internal_audit_id === $internalAudit->id, 404);

        try {
            $nc = $this->spawner->spawn($finding, $request->user()->id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', implode(' ', $e->validator->errors()->all()));
        }

        return redirect()
            ->route('internal_audits.show', $internalAudit)
            ->with('success', "{$finding->finding_no} raised non-conformity {$nc->ref_no}.");
    }

    // ---------------------------------------------------------------------

    private function auditorOptions(): array
    {
        return User::query()
            ->orderBy('surname')
            ->get(['id', 'name', 'middle_name', 'surname'])
            ->mapWithKeys(fn (User $u) => [
                $u->id => trim(implode(', ', array_filter([$u->surname, $u->name]))) ?: ('User #' . $u->id),
            ])
            ->all();
    }

    private function canPlan(Request $request): bool
    {
        return in_array((string) $request->user()?->user_type, self::PLAN_ROLES, true);
    }

    private function canConduct(Request $request): bool
    {
        return in_array((string) $request->user()?->user_type, self::CONDUCT_ROLES, true);
    }

    private function authorisePlan(Request $request): void
    {
        abort_unless($this->canPlan($request), 403, 'You are not allowed to plan internal audits.');
    }

    private function authoriseConduct(Request $request): void
    {
        abort_unless($this->canConduct($request), 403, 'You are not allowed to view internal audits.');
    }
}
