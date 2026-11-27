<?php

declare(strict_types=1);

namespace App\Http\Controllers\GodMode;

use App\Http\Controllers\Controller;
use App\Http\Requests\GodMode\AgencyRequest;
use App\Http\Requests\GodMode\VillageAgencyRequest;
use App\Models\Agency;
use App\Models\AgencyBillingPlan;
use App\Models\User;
use App\Services\GodMode\GodAgencyService;
use App\Support\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Card 6b0344184136c21243a7af8b — Agencies (clients) on the god plane.
 *
 * Every action here runs behind `['auth:godmode', 'godmode.only']`. There is
 * no policy call and no role check in this file: `EnsureIsGodMode` is the sole
 * gate (§6), and `AgencyPolicy` is deliberately NOT reused — its `before()`
 * passes `super_admin` so it would look like it worked, while its abilities
 * also pass `admin`, which is the wrong answer on this plane.
 *
 * There is no destroy action, on purpose and permanently (§1). Archive is a
 * status change; a hard delete exists only as a console command with
 * `--force`.
 */
class GodAgencyController extends Controller
{
    public function __construct(private readonly GodAgencyService $agencies)
    {
    }

    public function index(Request $request): View
    {
        $filters = [
            'q'      => trim((string) $request->query('q', '')),
            'status' => $this->statusFilter($request->query('status')),
            'empty'  => filter_var($request->query('empty', false), FILTER_VALIDATE_BOOLEAN),
            'sort'   => $request->query('sort'),
            'dir'    => $request->query('dir'),
            'per'    => $request->query('per'),
        ];

        $agencies = $this->agencies->paginate($filters);

        return view('godmode.agencies.index', [
            'agencies'     => $agencies,
            'filters'      => $filters,
            'statusCounts' => $this->agencies->statusCounts(),
            'perOptions'   => GodAgencyService::PER_PAGE_OPTIONS,
        ]);
    }

    public function create(): View
    {
        return view('godmode.agencies.create', [
            'agency' => new Agency(['status' => 'active', 'num_branches' => 1]),
        ]);
    }

    public function store(AgencyRequest $request): RedirectResponse
    {
        $payload = $request->identityPayload();

        // Creating a tenant the authenticated operator has no agency for is a
        // cross-tenant write, so it goes through the recorded bypass rather
        // than `withoutGlobalScopes()` (which would be invisible in the trail).
        $agency = Tenant::bypass(
            fn () => Agency::create($payload),
            'godmode: create an agency on the control plane',
            ['subdomain' => $payload['subdomain']]
        );

        $this->agencies->writeAudit($agency, 'created', null, $this->agencies->identity($agency), $request->user('godmode'));
        $this->agencies->writeActivity(
            GodAgencyService::AUDIT_ACTION_CREATED,
            $agency,
            "Agency {$agency->name} was created in god mode.",
            $request->user('godmode')
        );

        return redirect()
            ->route('godmode.agencies.show', $agency)
            ->with('status', "Agency {$agency->name} created.");    }

    /**
     * Card 6b07eb521243cfef572cd549 — create a tenant from the village modal.
     *
     * Three rows, one transaction: the agency, its first billing plan and its
     * first admin user. A half-created tenant is worse than a retry, because
     * the subdomain is taken and the operator cannot log in to fix it.
     *
     * Returns JSON: the modal submits with fetch() so validation errors can be
     * rendered field-by-field without losing what was typed. A normal form
     * post would redirect back to the village and wipe the modal.
     */
    public function storeFromVillage(VillageAgencyRequest $request): JsonResponse
    {
        $actor = $request->user('godmode');

        $created = DB::transaction(function () use ($request) {
            // Same recorded bypass as `store()`: an operator with no agency is
            // making a cross-tenant write, and the bypass is what makes that
            // visible in the tenant trail.
            $agency = Tenant::bypass(
                fn () => Agency::create($request->identityPayload()),
                'godmode: create an agency from the village',
                ['subdomain' => $request->input('subdomain')]
            );

            $plan = AgencyBillingPlan::create(
                $request->planPayload() + ['agency_id' => $agency->id]
            );

            $admin = User::create(
                $request->adminPayload() + [
                    'agency_id' => $agency->id,
                    'password'  => Hash::make((string) $request->input('admin_password')),
                ]
            );

            return [$agency, $plan, $admin];
        });

        [$agency, $plan, $admin] = $created;

        // The audit `after` payload carries the commercial figures and the
        // admin user id, so "who set this price and when" is answerable from
        // the audit row alone.
        $after = $this->agencies->identity($agency) + [
            'billing_plan_id'  => $plan->id,
            'initial_payment'  => $plan->initial_payment,
            'initial_terms'    => $plan->initial_terms,
            'monthly_amount'   => $plan->monthly_amount,
            'terms'            => $plan->terms,
            'starts_on'        => $plan->starts_on?->toDateString(),
            'admin_user_id'    => $admin->id,
            'admin_email'      => $admin->email,
        ];

        $this->agencies->writeAudit($agency, 'created', null, $after, $actor);
        $this->agencies->writeActivity(
            GodAgencyService::AUDIT_ACTION_CREATED,
            $agency,
            "Agency {$agency->name} was created from the village with a billing plan and an admin account.",
            $actor
        );

        return response()->json([
            'ok'        => true,
            'agency_id' => $agency->id,
            'name'      => $agency->name,
            'subdomain' => $agency->subdomain,
            'message'   => "Agency {$agency->name} created. Its admin can log in now; the subdomain URL "
                .'needs DNS and an Apache vhost, which is still a manual step (card 6b099ed8).',
        ], 201);
    }

    public function show(Agency $agency): View
    {
        return view('godmode.agencies.show', [
            'agency'    => $agency,
            'counts'    => $this->agencies->affectedPeople($agency),
            'history'   => $this->agencies->statusHistory($agency),
            'activity'  => $this->agencies->recentActivity($agency),
            'fullHost'  => $this->agencies->fullHost($agency->subdomain),
        ]);
    }

    public function edit(Agency $agency): View
    {
        return view('godmode.agencies.edit', [
            'agency'   => $agency,
            'fullHost' => $this->agencies->fullHost($agency->subdomain),
        ]);
    }

    public function update(AgencyRequest $request, Agency $agency): RedirectResponse
    {
        $payload = $request->identityPayload();
        $before = $this->agencies->identity($agency);

        Tenant::bypass(
            fn () => $agency->update($payload),
            'godmode: update an agency on the control plane',
            ['agency_id' => $agency->id]
        );

        $after = $this->agencies->identity($agency->fresh());
        $diff = array_filter($after, fn ($value, $key) => ($before[$key] ?? null) !== $value, ARRAY_FILTER_USE_BOTH);

        // An untouched form is not an event. Writing `updated` with an empty
        // diff would bury the real changes in the history.
        if ($diff !== []) {
            $this->agencies->writeAudit(
                $agency,
                'updated',
                array_intersect_key($before, $diff),
                $diff,
                $request->user('godmode')
            );

            $this->agencies->writeActivity(
                GodAgencyService::AUDIT_ACTION_UPDATED,
                $agency,
                "Agency {$agency->name} was updated in god mode (".implode(', ', array_keys($diff)).').',
                $request->user('godmode')
            );
        }

        return redirect()
            ->route('godmode.agencies.show', $agency)
            ->with('status', $agency->name.' saved.');
    }

    /**
     * Archive. POST + CSRF; the tenant URL stops resolving because
     * `IdentifyAgency` only binds `status = active`.
     */
    public function archive(Request $request, Agency $agency): RedirectResponse
    {
        return $this->setStatus($request, $agency, 'inactive');
    }

    public function activate(Request $request, Agency $agency): RedirectResponse
    {
        return $this->setStatus($request, $agency, 'active');
    }

    private function setStatus(Request $request, Agency $agency, string $status): RedirectResponse
    {
        $operator = $request->user('godmode');

        if ($agency->status === $status) {
            return $this->backToList($request)->with('status', $agency->name.' is already '.$status.'.');
        }

        $before = ['status' => $agency->status];

        Tenant::bypass(
            fn () => $agency->update(['status' => $status]),
            'godmode: change an agency status on the control plane',
            ['agency_id' => $agency->id, 'status' => $status]
        );

        $this->agencies->writeAudit($agency, $status === 'active' ? 'approved' : 'archived', $before, ['status' => $status], $operator);
        $this->agencies->writeActivity(
            $status === 'active' ? 'godmode.agency_activated' : 'godmode.agency_archived',
            $agency,
            "Agency {$agency->name} was ".($status === 'active' ? 'activated' : 'archived')." in god mode.",
            $operator
        );

        return $this->backToList($request)->with('status', $agency->name.' is now '.$status.'.');
    }

    /**
     * "Returns to the list with filters preserved" (§5). The `_return` value is
     * the list's own query string, posted as a hidden field and re-validated
     * as a query string so it cannot become an open redirect: `to()` with a
     * full URL is refused, and only the known parameters survive.
     */
    private function backToList(Request $request): RedirectResponse
    {
        $params = [];

        parse_str((string) $request->input('_return', ''), $params);

        $safe = array_filter([
            'q'      => $params['q'] ?? null,
            'status' => $this->statusFilter($params['status'] ?? null),
            'empty'  => ! empty($params['empty']) ? 1 : null,
            'sort'   => in_array($params['sort'] ?? null, GodAgencyService::SORTS, true) ? $params['sort'] : null,
            'dir'    => in_array($params['dir'] ?? null, ['asc', 'desc'], true) ? $params['dir'] : null,
            'per'    => in_array((int) ($params['per'] ?? 0), GodAgencyService::PER_PAGE_OPTIONS, true) ? $params['per'] : null,
            'page'   => (int) ($params['page'] ?? 0) > 0 ? (int) $params['page'] : null,
        ], fn ($value) => $value !== null && $value !== '');

        return redirect()->route('godmode.agencies.index', $safe);
    }

    /** Unknown/removed status values are treated as "all", never as a filter that matches nothing. */
    private function statusFilter($status): ?string
    {
        return in_array($status, ['active', 'pending', 'inactive'], true) ? $status : null;
    }
}
