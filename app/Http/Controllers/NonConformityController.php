<?php

namespace App\Http\Controllers;

use App\Models\CorrectiveAction;
use App\Models\NonConformity;
use App\Models\User;
use App\Services\NonConformityClosure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * QMS-16 (card 6afa0dad2729295c62765c76): non-conformity and CAPA screens.
 *
 * The UI over QMS-15's spine. The list is severity/status badged and flags
 * overdue work; the detail screen is where containment, root cause and the
 * child corrective actions are recorded; closure is only offered once every
 * action has been verified *and* its effectiveness reviewed.
 *
 * The closure rule itself lives in NonConformityClosure and is enforced again
 * by the model on save. This controller only asks it for the reasons so the
 * button can be disabled with a real explanation instead of failing on click.
 *
 * Roles mirror complaints: read for super_admin / admin / qms_manager /
 * internal_auditor, manage for the first three. The auditor audits the CAPA
 * process, so it reads the register but cannot sign anything off itself.
 */
class NonConformityController extends Controller
{
    /** Roles allowed to record and close non-conformities and actions. */
    private const MANAGE_ROLES = ['super_admin', 'admin', 'qms_manager'];

    /** Roles allowed to read the register and the detail screen. */
    private const READ_ROLES = ['super_admin', 'admin', 'qms_manager', 'internal_auditor'];

    private const TABS = [
        'all'     => 'All',
        'open'    => 'Open',
        'overdue' => 'Overdue',
        'closed'  => 'Closed',
    ];

    public function __construct(private readonly NonConformityClosure $closure)
    {
    }

    public function index(Request $request): View
    {
        $this->authoriseRead($request);

        $filters = $request->validate([
            'tab'      => ['nullable', 'string'],
            'q'        => ['nullable', 'string', 'max:120'],
            'severity' => ['nullable', Rule::in(array_keys(NonConformity::SEVERITIES))],
        ]);

        $tab = $filters['tab'] ?? 'all';
        if (! array_key_exists($tab, self::TABS)) {
            $tab = 'all';
        }

        $query = NonConformity::query()
            ->with(['owner'])
            ->withCount('correctiveActions')
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('ref_no', 'like', '%' . $term . '%')
                          ->orWhere('description', 'like', '%' . $term . '%')
                          ->orWhere('process_area', 'like', '%' . $term . '%');
                });
            })
            ->when($filters['severity'] ?? null, fn ($q, $severity) => $q->where('severity', $severity));

        if ($tab === 'overdue') {
            // Past due and not closed. Mirrors the model's is_overdue accessor.
            $query->where('status', '!=', NonConformity::STATUS_CLOSED)
                  ->whereNotNull('due_date')
                  ->whereDate('due_date', '<', now()->startOfDay());
        } elseif ($tab === 'open') {
            $query->where('status', '!=', NonConformity::STATUS_CLOSED);
        } elseif ($tab === 'closed') {
            $query->where('status', NonConformity::STATUS_CLOSED);
        }

        $nonConformities = $query
            // Closed last, then the most severe. Portable CASE, not MySQL FIELD().
            ->orderByRaw('CASE WHEN status = ? THEN 1 ELSE 0 END', [NonConformity::STATUS_CLOSED])
            ->orderByRaw("CASE severity WHEN 'critical' THEN 0 WHEN 'major' THEN 1 ELSE 2 END")
            ->orderByDesc('detected_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('non_conformities.index', [
            'nonConformities' => $nonConformities,
            'filters'         => $filters,
            'tab'             => $tab,
            'tabs'            => self::TABS,
            'severities'      => NonConformity::SEVERITIES,
            'counts'          => $this->tabCounts(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorise($request);

        return view('non_conformities.create', [
            'nextRef'   => $this->suggestNextRef(),
            'sources'   => NonConformity::SOURCES,
            'severities' => NonConformity::SEVERITIES,
            'owners'    => $this->ownerOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorise($request);

        $agencyId = $this->resolveAgencyId();
        abort_if($agencyId === null, 403, 'No agency context.');

        $validated = $request->validate([
            'ref_no'               => [
                'required', 'string', 'max:64',
                Rule::unique('non_conformities', 'ref_no')->where('agency_id', $agencyId),
            ],
            'source'               => ['required', Rule::in(array_keys(NonConformity::SOURCES))],
            'detected_date'        => ['required', 'date'],
            'process_area'         => ['nullable', 'string', 'max:128'],
            'description'          => ['required', 'string', 'max:5000'],
            'requirement_breached' => ['nullable', 'string', 'max:5000'],
            'severity'             => ['required', Rule::in(array_keys(NonConformity::SEVERITIES))],
            'owner_id'             => ['nullable', 'integer', 'exists:users,id'],
            'due_date'             => ['nullable', 'date'],
        ], [
            'ref_no.unique' => 'That reference is already in use for this agency.',
        ]);

        $nc = NonConformity::create([
            ...$validated,
            'agency_id'  => $agencyId,
            'status'     => NonConformity::STATUS_OPEN,
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('non_conformities.show', $nc)
            ->with('success', "{$nc->ref_no} recorded.");
    }

    public function show(Request $request, NonConformity $nonConformity): View
    {
        $this->authoriseRead($request);

        $nonConformity->load(['owner', 'creator', 'correctiveActions.owner', 'correctiveActions.verifier']);

        return view('non_conformities.show', [
            'nc'         => $nonConformity,
            'canManage'  => $this->canManage($request),
            'blockers'   => $this->closure->closureBlockers($nonConformity),
            'owners'     => $this->canManage($request) ? $this->ownerOptions() : [],
            'statuses'   => NonConformity::STATUS_LABELS,
        ]);
    }

    /** Save the containment and root-cause narrative, and move the status. */
    public function update(Request $request, NonConformity $nonConformity): RedirectResponse
    {
        $this->authorise($request);

        $validated = $request->validate([
            'containment'          => ['nullable', 'string', 'max:5000'],
            'root_cause'           => ['nullable', 'string', 'max:5000'],
            'requirement_breached' => ['nullable', 'string', 'max:5000'],
            'process_area'         => ['nullable', 'string', 'max:128'],
            'severity'             => ['required', Rule::in(array_keys(NonConformity::SEVERITIES))],
            'owner_id'             => ['nullable', 'integer', 'exists:users,id'],
            'due_date'             => ['nullable', 'date'],
            'status'               => ['required', Rule::in(NonConformity::STATUSES)],
        ]);

        // Closing goes through close() so the gate and its message are shared
        // with the dedicated button; a status edit must not be a back door.
        if ($validated['status'] === NonConformity::STATUS_CLOSED) {
            unset($validated['status']);

            return $this->close($request, $nonConformity, $validated);
        }

        $nonConformity->update($validated);

        return redirect()
            ->route('non_conformities.show', $nonConformity)
            ->with('success', "{$nonConformity->ref_no} updated.");
    }

    /** Close, or refuse with the reasons why not. */
    public function close(Request $request, NonConformity $nonConformity, array $extra = []): RedirectResponse
    {
        $this->authorise($request);

        $blockers = $this->closure->closureBlockers($nonConformity);

        if ($blockers !== []) {
            return redirect()
                ->route('non_conformities.show', $nonConformity)
                ->with('error', 'This non-conformity cannot be closed yet: ' . implode(' ', $blockers));
        }

        $nonConformity->update([...$extra, 'status' => NonConformity::STATUS_CLOSED]);

        return redirect()
            ->route('non_conformities.show', $nonConformity)
            ->with('success', "{$nonConformity->ref_no} closed. Every action was verified and effectiveness reviewed.");
    }

    // ---------------------------------------------------------------------
    // Corrective actions
    // ---------------------------------------------------------------------

    public function storeAction(Request $request, NonConformity $nonConformity): RedirectResponse
    {
        $this->authorise($request);

        abort_if($nonConformity->is_closed, 403, 'A closed non-conformity cannot take new corrective actions.');

        $validated = $request->validate([
            'action_text' => ['required', 'string', 'max:5000'],
            'owner_id'    => ['nullable', 'integer', 'exists:users,id'],
            'due_date'    => ['nullable', 'date'],
        ]);

        CorrectiveAction::create([
            ...$validated,
            'agency_id'         => $nonConformity->agency_id,
            'non_conformity_id' => $nonConformity->id,
            'created_by'        => $request->user()?->id,
            'status'            => CorrectiveAction::STATUS_PENDING,
        ]);

        return redirect()
            ->route('non_conformities.show', $nonConformity)
            ->with('success', 'Corrective action added.');
    }

    /**
     * Record completion, verification and effectiveness on one action.
     *
     * Each stage stamps its own date, and none of them can be recorded out of
     * order: effectiveness of an action nobody verified means nothing.
     */
    public function updateAction(Request $request, NonConformity $nonConformity, CorrectiveAction $action): RedirectResponse
    {
        $this->authorise($request);

        abort_unless($action->non_conformity_id === $nonConformity->id, 404);

        $validated = $request->validate([
            'completed_date'            => ['nullable', 'date'],
            'verification_notes'        => ['nullable', 'string', 'max:5000'],
            'effectiveness_notes'       => ['nullable', 'string', 'max:5000'],
            'complete'                  => ['nullable', 'boolean'],
            'verify'                    => ['nullable', 'boolean'],
            'effectiveness'             => ['nullable', 'boolean'],
        ]);

        $attributes = array_filter([
            'completed_date'      => $validated['completed_date'] ?? null,
            'verification_notes'  => $validated['verification_notes'] ?? null,
            'effectiveness_notes' => $validated['effectiveness_notes'] ?? null,
        ], fn ($v) => $v !== null);

        if ($request->boolean('complete') && $action->completed_date === null) {
            $attributes['completed_date'] = now()->toDateString();
        }

        if ($request->boolean('verify')) {
            if (($attributes['completed_date'] ?? $action->completed_date) === null) {
                return $this->actionError($nonConformity, 'An action cannot be verified before it is marked complete.');
            }

            if ($action->verified_at === null) {
                $attributes['verified_at'] = now();
                $attributes['verified_by'] = $request->user()?->id;
            }
        }

        if ($request->boolean('effectiveness')) {
            if ($action->verified_at === null && ! isset($attributes['verified_at'])) {
                return $this->actionError($nonConformity, 'Effectiveness cannot be reviewed before the action is verified.');
            }

            if ($action->effectiveness_verified_at === null) {
                $attributes['effectiveness_verified_at'] = now();
            }
        }

        $action->update($attributes);

        return redirect()
            ->route('non_conformities.show', $nonConformity)
            ->with('success', 'Corrective action updated.');
    }

    public function destroyAction(Request $request, NonConformity $nonConformity, CorrectiveAction $action): RedirectResponse
    {
        $this->authorise($request);

        abort_unless($action->non_conformity_id === $nonConformity->id, 404);

        $action->delete();

        return redirect()
            ->route('non_conformities.show', $nonConformity)
            ->with('success', 'Corrective action removed.');
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function actionError(NonConformity $nc, string $message): RedirectResponse
    {
        return redirect()->route('non_conformities.show', $nc)->with('error', $message);
    }

    /** @return array<string,int> */
    private function tabCounts(): array
    {
        return [
            'all'     => NonConformity::query()->count(),
            'open'    => NonConformity::query()->where('status', '!=', NonConformity::STATUS_CLOSED)->count(),
            'overdue' => NonConformity::query()
                ->where('status', '!=', NonConformity::STATUS_CLOSED)
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->startOfDay())
                ->count(),
            'closed'  => NonConformity::query()->where('status', NonConformity::STATUS_CLOSED)->count(),
        ];
    }

    private function suggestNextRef(): string
    {
        $last = NonConformity::withTrashed()
            ->where('ref_no', 'like', 'NC-%')
            ->pluck('ref_no')
            ->map(fn ($ref) => (int) preg_replace('/\D/', '', (string) $ref))
            ->max() ?? 0;

        return 'NC-' . str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
    }

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

    private function canManage(Request $request): bool
    {
        return in_array((string) $request->user()?->user_type, self::MANAGE_ROLES, true);
    }

    private function authorise(Request $request): void
    {
        abort_unless($this->canManage($request), 403, 'You are not allowed to manage non-conformities.');
    }

    private function authoriseRead(Request $request): void
    {
        abort_unless(
            in_array((string) $request->user()?->user_type, self::READ_ROLES, true),
            403,
            'You are not allowed to view non-conformities.'
        );
    }
}
