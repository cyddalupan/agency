<?php

declare(strict_types=1);

namespace App\Services\GodMode;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\AuditEvent;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Card 6b0344184136c21243a7af8b — the god-plane Agency console.
 *
 * One place decides what "agency", "empty", "counts" and "reserved subdomain"
 * mean, so the list, the drawers and the confirmation copy can never disagree
 * with each other.
 *
 * Two rules from the spec are load-bearing here and are easy to undo by
 * accident:
 *
 *   1. NO `Tenant::bypass()` for the list. `TenantScope` already drops the
 *      filter for a `super_admin`, and the god guard only ever admits one; an
 *      explicit bypass would write an `audit_events` row per page view (§4).
 *      Cross-tenant *writes* (create, archive) go through `bypass()` so they
 *      are recorded like the god login is.
 *
 *   2. Counts are subqueries on the list query, never per-row lazy loads.
 *      20 rows today is 80 queries; the same code one year from now is 2,000
 *      pages and the N+1 stops being a rounding error.
 */
class GodAgencyService
{
    /** The audit/activity action slugs for this console. Prefixed `godmode.` so
     * a control-plane agency change is distinguishable from the tenant screen's
     * `agency_activated`/`agency_deactivated` in the same `activity_logs` table. */
    public const AUDIT_ACTION_CREATED = 'godmode.agency_created';
    public const AUDIT_ACTION_UPDATED = 'godmode.agency_updated';

    /** Statuses an operator may set. `pending` is reachable only by registration. */
    public const SELECTABLE_STATUSES = ['active', 'inactive'];

    public const PER_PAGE_OPTIONS = [25, 50, 100];

    public const SORTS = ['name', 'subdomain', 'status', 'applicants', 'users', 'created'];

    /**
     * Subdomain labels that are (or may become) infrastructural. Rejecting
     * `godmode` is not cosmetic: a tenant on that label would collide with the
     * control plane, which `IdentifyAgency` refuses to serve from a tenant
     * host. The list is the spec's, verbatim.
     */
    public const RESERVED_SUBDOMAINS = [
        'www', 'api', 'godmode', 'admin', 'mail', 'portal', 'app', 'static',
    ];

    /**
     * The list query with its four count subqueries attached.
     *
     * `applicants_count`, `users_count` and `branches_count` come back as
     * integers under those names, which is what the view reads. `whereNotExists`
     * is deliberately not used here: "empty" is a combination of two counts
     * and is answered by `onlyEmpty()` from the same subqueries.
     */
    public function query(): Builder
    {
        return Agency::query()
            ->select('agencies.*')
            ->selectSub(fn ($q) => $q->from('applicants')->whereColumn('applicants.agency_id', 'agencies.id')->selectRaw('count(*)'), 'applicants_count')
            ->selectSub(fn ($q) => $q->from('users')->whereColumn('users.agency_id', 'agencies.id')->selectRaw('count(*)'), 'users_count')
            ->selectSub(fn ($q) => $q->from('branches')->whereColumn('branches.agency_id', 'agencies.id')->selectRaw('count(*)'), 'branches_count');
    }

    /**
     * The one entry point the controller uses, so every filter combination is
     * exercised through the same code in tests and in production.
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $per = $this->perPage($filters['per'] ?? null);
        $sort = in_array(($filters['sort'] ?? ''), self::SORTS, true) ? $filters['sort'] : null;
        $dir = ($filters['dir'] ?? '') === 'desc' ? 'desc' : 'asc';

        return $this->query()
            ->when(filled($filters['q'] ?? null), fn (Builder $q) => $this->applySearch($q, (string) $filters['q']))
            ->when(filled($filters['status'] ?? null), fn (Builder $q) => $q->where('agencies.status', $filters['status']))
            ->when(filter_var($filters['empty'] ?? false, FILTER_VALIDATE_BOOLEAN) === true, fn (Builder $q) => $this->onlyEmpty($q))
            ->when($sort, fn (Builder $q) => $this->applySort($q, (string) $sort, $dir), fn (Builder $q) => $this->defaultSort($q))
            ->orderBy('agencies.id', 'asc')
            ->paginate($per)
            ->withQueryString();
    }

    /**
     * Default order: pending first, then name. Pending is the only queue that
     * blocks a customer; putting it under "Z" would be a silent SLO breach.
     */
    public function defaultSort(Builder $query): Builder
    {
        return $query
            ->orderByRaw("case when agencies.status = 'pending' then 0 else 1 end")
            ->orderBy('agencies.name')
            ->orderBy('agencies.id');
    }

    public function applySort(Builder $query, string $sort, string $dir): Builder
    {
        return match ($sort) {
            'applicants' => $query->orderBy('applicants_count', $dir),
            'users'      => $query->orderBy('users_count', $dir),
            'created'    => $query->orderBy('agencies.created_at', $dir),
            'status'     => $query->orderBy('agencies.status', $dir),
            'subdomain'  => $query->orderBy('agencies.subdomain', $dir),
            default      => $query->orderBy('agencies.name', $dir),
        };
    }

    /**
     * `LIKE %q%` across name, subdomain, email, contact person.
     *
     * `%` and `_` are escaped on the way in and the LIKE gets an explicit
     * ESCAPE character: typing a single `%` must return zero rows, not the
     * whole table. MySQL's default backslash escape for LIKE is a
     * `NO_BACKSLASH_ESCAPES` away from doing nothing, so it is stated here.
     *
     * SQLite (tests) needs the same ESCAPE clause to be honoured, which it is.
     */
    public function applySearch(Builder $query, string $term): Builder
    {
        $needle = '%'.$this->escapeLike($term).'%';

        return $query->where(function (Builder $q) use ($needle) {
            foreach (['name', 'subdomain', 'email', 'contact_person'] as $column) {
                $q->orWhereRaw('agencies.'.$column.' LIKE ? ESCAPE ?', [$needle, '\\']);
            }
        });
    }

    /** Escape the LIKE wildcards and the escape character itself. */
    public function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    /**
     * "Empty" = no users AND no applicants. This is the operator's actual need
     * behind half the demand for a delete button, so it is a filter on the
     * list rather than a destructive action.
     */
    public function onlyEmpty(Builder $query): Builder
    {
        return $query
            ->whereNotExists(fn ($q) => $q->from('users')->whereColumn('users.agency_id', 'agencies.id'))
            ->whereNotExists(fn ($q) => $q->from('applicants')->whereColumn('applicants.agency_id', 'agencies.id'));
    }

    public function perPage($requested): int
    {
        $per = (int) $requested;

        return in_array($per, self::PER_PAGE_OPTIONS, true) ? $per : 25;
    }

    /**
     * Whole-set counts for the status tabs. Deliberately NOT filtered by the
     * search box: the tabs answer "how many pending exist", and a count that
     * moves while you type is a count you cannot act on.
     */
    public function statusCounts(): array
    {
        $rows = DB::table('agencies')
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = ['all' => 0];

        foreach (['active', 'pending', 'inactive'] as $status) {
            $counts[$status] = (int) ($rows[$status] ?? 0);
            $counts['all'] += $counts[$status];
        }

        return $counts;
    }

    /** Archive/activate confirmation copy: names the agency AND what a lock-out costs. */
    public function affectedPeople(Agency $agency): array
    {
        return [
            'applicants' => Applicant::withoutGlobalScopes()->where('agency_id', $agency->id)->count(),
            'users'      => User::withoutGlobalScopes()->where('agency_id', $agency->id)->count(),
            'branches'   => Branch::withoutGlobalScopes()->where('agency_id', $agency->id)->count(),
        ];
    }

    /** Display form of a stored subdomain: the bare label, or null. */
    public function fullHost(?string $subdomain): ?string
    {
        $label = trim((string) $subdomain);

        return $label === '' ? null : $label.$this->tenantBaseDomain();
    }

    public function tenantBaseDomain(): string
    {
        return '.landas.fixitautoservices.com';
    }

    public function isReserved(string $subdomain): bool
    {
        return in_array(strtolower($subdomain), self::RESERVED_SUBDOMAINS, true);
    }

    /**
     * What the operator typed -> the bare label.
     *
     * Accepts `my-agency`, `my-agency.landas.fixitautoservices.com`,
     * `https://my-agency.landas.fixitautoservices.com/path`. Same shape as
     * AgencyController::parseSubdomain(), which is the tenant screen's rule;
     * shared here rather than duplicated because the two screens must agree
     * on which label a host means.
     */
    public function normaliseSubdomain(?string $input): string
    {
        $raw = trim((string) $input);

        if ($raw === '') {
            return '';
        }

        $withScheme = preg_match('~^[a-z][a-z0-9+.-]*://~i', $raw) ? $raw : 'http://'.$raw;
        $host = parse_url($withScheme, PHP_URL_HOST) ?: $raw;

        return strtolower(explode('.', $host)[0]);
    }

    /**
     * God-plane audit row. Created events carry the new values in `after`;
     * updates carry the diff only, so the drawer's history reads
     * "status: active -> inactive" instead of dumping both records.
     */
    public function writeAudit(Agency $agency, string $action, ?array $before, ?array $after, User $actor): AuditEvent
    {
        return AuditEvent::withoutGlobalScopes()->create([
            'agency_id'     => $agency->id,
            'actor_user_id' => $actor->id,
            'action'        => $action,
            'subject_type'  => Agency::class,
            'subject_id'    => $agency->id,
            'subject_label' => mb_substr((string) $agency->name, 0, 255),
            'before'        => $before,
            'after'         => $after,
            'ip_address'    => request()?->ip(),
            'user_agent'    => request()?->userAgent()
                ? mb_substr((string) request()->userAgent(), 0, 512)
                : null,
        ]);
    }

    /**
     * `activity_logs` row, written with agency_id NULL: the actor is a god
     * operator with no agency, and attributing the row to the *subject* agency
     * would file an operator action inside the tenant it targets.
     */
    public function writeActivity(string $action, Agency $agency, string $description, User $actor): ActivityLog
    {
        return ActivityLog::create([
            'agency_id'    => null,
            'user_id'      => $actor->id,
            'subject_type' => Agency::class,
            'subject_id'   => $agency->id,
            'action'       => $action,
            'description'  => $description,
            'metadata'     => ['channel' => 'godmode'],
        ]);
    }

    public function statusHistory(Agency $agency, int $limit = 10)
    {
        return AuditEvent::withoutGlobalScopes()
            ->where('subject_type', Agency::class)
            ->where('subject_id', $agency->id)
            ->with('actor')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function recentActivity(Agency $agency, int $limit = 10)
    {
        return ActivityLog::query()
            ->where('agency_id', $agency->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /** The field set this console writes. Everything else is read-only here. */
    public function identity(Agency $agency): array
    {
        return [
            'name'           => $agency->name,
            'subdomain'      => $agency->subdomain,
            'email'          => $agency->email,
            'contact_person' => $agency->contact_person,
            'city'           => $agency->city,
            'address'        => $agency->address,
            'num_branches'   => $agency->num_branches,
            'status'         => $agency->status,
        ];
    }
}
