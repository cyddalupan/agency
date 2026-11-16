<?php

namespace App\Http\Controllers;

use App\Models\Risk;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * QMS-12 (card 6afa0da98260dda52823960e): risk register screens.
 *
 * The UI over the QMS-11 spine: a filtered, score-sortable register, a
 * create/edit form with an explicit likelihood/impact picker, and the 5×5
 * matrix view that shows where the agency's exposure actually sits.
 *
 * Roles mirror document control: read for super_admin / admin / qms_manager /
 * internal_auditor, manage (create/edit/delete) for the first three. The
 * Internal Auditor audits the register; it does not get to write it.
 */
class RiskController extends Controller
{
    /** Roles allowed to create, edit and delete risks. */
    private const MANAGE_ROLES = ['super_admin', 'admin', 'qms_manager'];

    /** Roles allowed to read the register and the matrix. */
    private const READ_ROLES = ['super_admin', 'admin', 'qms_manager', 'internal_auditor'];

    /** The register: filtered list, sorted by score descending by default. */
    public function index(Request $request): View
    {
        $this->authoriseRead($request);

        $filters = $request->validate([
            'q'      => ['nullable', 'string', 'max:120'],
            'level'  => ['nullable', Rule::in(Risk::LEVELS)],
            'status' => ['nullable', Rule::in(Risk::STATUSES)],
            'sort'   => ['nullable', Rule::in(['score_desc', 'score_asc', 'ref', 'review_date'])],
        ]);

        $sort = $filters['sort'] ?? 'score_desc';

        $risks = Risk::query()
            ->with(['riskOwner'])
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('ref_no', 'like', '%' . $term . '%')
                          ->orWhere('title', 'like', '%' . $term . '%')
                          ->orWhere('process_area', 'like', '%' . $term . '%');
                });
            })
            ->when($filters['level'] ?? null, fn ($q, $level) => $q->where('risk_level', $level))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderBy(...match ($sort) {
                'score_asc'   => ['risk_score', 'asc'],
                'ref'         => ['ref_no', 'asc'],
                'review_date' => ['review_date', 'asc'],
                default       => ['risk_score', 'desc'],
            })
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        return view('risks.index', [
            'risks'   => $risks,
            'filters' => $filters,
            'levels'  => Risk::LEVEL_LABELS,
            'statuses' => Risk::STATUS_LABELS,
            'sort'    => $sort,
        ]);
    }

    /** The 5×5 matrix: every open risk placed in its likelihood/impact cell. */
    public function matrix(Request $request): View
    {
        $this->authoriseRead($request);

        // Closed risks are left out: the matrix is a picture of live exposure,
        // and a closed risk sitting in a red cell misrepresents the agency.
        $risks = Risk::query()
            ->with('riskOwner')
            ->where('status', '!=', Risk::STATUS_CLOSED)
            ->orderByDesc('risk_score')
            ->get();

        // grid[likelihood][impact] => risks
        $grid = [];
        foreach (range(Risk::SCALE_MIN, Risk::SCALE_MAX) as $likelihood) {
            foreach (range(Risk::SCALE_MIN, Risk::SCALE_MAX) as $impact) {
                $grid[$likelihood][$impact] = [];
            }
        }

        foreach ($risks as $risk) {
            $likelihood = (int) $risk->likelihood;
            $impact     = (int) $risk->impact;

            // Defensive: a row outside the scale would otherwise be dropped
            // silently, so clamp it into the nearest cell instead.
            $likelihood = max(Risk::SCALE_MIN, min(Risk::SCALE_MAX, $likelihood));
            $impact     = max(Risk::SCALE_MIN, min(Risk::SCALE_MAX, $impact));

            $grid[$likelihood][$impact][] = $risk;
        }

        return view('risks.matrix', [
            'grid'     => $grid,
            'total'    => $risks->count(),
            'scale'    => range(Risk::SCALE_MIN, Risk::SCALE_MAX),
            'levelFor' => fn (int $l, int $i) => Risk::levelForPair($l, $i),
            'labelFor' => fn (int $l, int $i) => Risk::LEVEL_LABELS[Risk::levelForPair($l, $i)],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorise($request);

        return view('risks.create', [
            'owners'  => $this->ownerOptions(),
            'levels'  => Risk::LEVEL_LABELS,
            'statuses' => Risk::STATUS_LABELS,
            'scale'   => range(Risk::SCALE_MIN, Risk::SCALE_MAX),
            'nextRef' => $this->suggestNextRef(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorise($request);

        $agencyId = $this->resolveAgencyId();
        abort_if($agencyId === null, 403, 'No agency context.');

        $validated = $this->validateRisk($request, $agencyId);

        $risk = Risk::create([
            ...$validated,
            'agency_id' => $agencyId,
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('risks.index')
            ->with('success', "{$risk->ref_no} created (score {$risk->risk_score}, {$risk->level_label}).");
    }

    public function edit(Request $request, Risk $risk): View
    {
        $this->authorise($request);

        return view('risks.edit', [
            'risk'     => $risk,
            'owners'   => $this->ownerOptions(),
            'levels'   => Risk::LEVEL_LABELS,
            'statuses' => Risk::STATUS_LABELS,
            'scale'    => range(Risk::SCALE_MIN, Risk::SCALE_MAX),
        ]);
    }

    public function update(Request $request, Risk $risk): RedirectResponse
    {
        $this->authorise($request);

        $validated = $this->validateRisk($request, (int) $risk->agency_id, $risk->id);

        $risk->update($validated);

        return redirect()
            ->route('risks.index')
            ->with('success', "{$risk->ref_no} updated (score {$risk->risk_score}, {$risk->level_label}).");
    }

    public function destroy(Request $request, Risk $risk): RedirectResponse
    {
        $this->authorise($request);

        $ref = $risk->ref_no;
        $risk->delete();

        return redirect()
            ->route('risks.index')
            ->with('success', "{$ref} deleted.");
    }

    /**
     * Shared validation for store and update.
     *
     * `ref_no` is unique per agency, soft-deleted rows included (the DB index
     * says so), hence `withTrashed()` on the uniqueness ignore lookup.
     */
    private function validateRisk(Request $request, int $agencyId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'ref_no'           => [
                'required', 'string', 'max:64',
                Rule::unique('risks', 'ref_no')
                    ->where(fn ($q) => $q->where('agency_id', $agencyId))
                    ->ignore($ignoreId),
            ],
            'title'            => ['required', 'string', 'max:255'],
            'process_area'     => ['nullable', 'string', 'max:128'],
            'description'      => ['nullable', 'string', 'max:5000'],
            'cause'            => ['nullable', 'string', 'max:5000'],
            'consequence'      => ['nullable', 'string', 'max:5000'],
            'likelihood'       => ['required', 'integer', 'min:' . Risk::SCALE_MIN, 'max:' . Risk::SCALE_MAX],
            'impact'           => ['required', 'integer', 'min:' . Risk::SCALE_MIN, 'max:' . Risk::SCALE_MAX],
            'existing_controls' => ['nullable', 'string', 'max:5000'],
            'treatment_action' => ['nullable', 'string', 'max:5000'],
            'risk_owner_id'    => ['nullable', 'integer', 'exists:users,id'],
            'target_date'      => ['nullable', 'date'],
            'review_date'      => ['nullable', 'date'],
            'status'           => ['required', Rule::in(Risk::STATUSES)],
        ], [
            'ref_no.unique' => 'That reference is already in use for this agency.',
        ]);
    }

    /** Next free reference in the RISK-nnn series, for convenience. */
    private function suggestNextRef(): string
    {
        $last = Risk::withTrashed()
            ->where('ref_no', 'like', 'RISK-%')
            ->pluck('ref_no')
            ->map(fn ($ref) => (int) preg_replace('/\D/', '', (string) $ref))
            ->max() ?? 0;

        return 'RISK-' . str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
    }

    private function authorise(Request $request): void
    {
        abort_unless(
            in_array((string) $request->user()?->user_type, self::MANAGE_ROLES, true),
            403,
            'You are not allowed to manage the risk register.'
        );
    }

    private function authoriseRead(Request $request): void
    {
        abort_unless(
            in_array((string) $request->user()?->user_type, self::READ_ROLES, true),
            403,
            'You are not allowed to view the risk register.'
        );
    }

    /** Risk-owner dropdown: users of this agency. */
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
