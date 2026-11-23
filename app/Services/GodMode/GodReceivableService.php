<?php

declare(strict_types=1);

namespace App\Services\GodMode;

use App\Models\Agency;
use App\Models\Receivable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Card 6b0342ac4cf7cfdc3e26d6a9 — the god-plane money ledger.
 *
 * Read-only, cross-tenant, one definition. Exactly as `GodAgencyService` owns
 * "agency" and "counts", this class owns "collected", "outstanding", "voided",
 * the month window, the buckets and the currency format. The list, the
 * per-agency drill-in and the dashboard all read the same methods, so the
 * three screens cannot disagree about a single centavo (§4.3).
 *
 * Load-bearing rules, each one easy to undo by accident:
 *
 *   1. NO `Tenant::bypass()`. `TenantScope` already drops the filter for a
 *      `super_admin`, and `godmode.only` only ever admits one; an explicit
 *      bypass would write an `audit_events` row per page view (§4.2).
 *
 *   2. No writes. This plane reports money an agency entered; an operator edit
 *      would break the `receivables` -> `receivable_histories` chain (§0).
 *
 *   3. Voided money is VISIBLE. `cancelled` and soft-deleted rows are excluded
 *      from collected/outstanding but counted and listed with a badge (§4.4).
 *      Hiding them would make a voucher vanish without a trace.
 *
 *   4. Amounts are decimal STRINGS from the model cast (`decimal:2`). Every
 *      sum happens in SQL; PHP only formats. No float arithmetic on money.
 */
class GodReceivableService
{
    /** Rows per page on the ledger and on the drill-in. */
    public const PER_PAGE_OPTIONS = [25, 50, 100];

    /** Month buckets the chart and the series always carry. */
    public const SERIES_MONTHS = 12;

    /** Ageing buckets for outstanding money, in days since `date`. */
    public const AGEING_BUCKETS = ['0-30', '31-60', '61-90', '90+'];

    /** What a NULL `account` renders as. Never dropped (§3.3.4). */
    public const UNSPECIFIED_ACCOUNT = '(unspecified)';

    /** Dashboard "top agencies" list size. */
    public const TOP_AGENCIES = 5;

    /** @var array<string, float> */
    private array $seriesCache = [];

    /** @var array<string, array> */
    private array $summaryCache = [];

    /** @var array<int, Agency> */
    private array $agencyCache = [];

    // ── window ───────────────────────────────────────────────────────

    /**
     * One date parser for both screens: `YYYY-MM` in, a first-of-month Carbon
     * out, or null for anything unparseable.
     */
    public function parseMonth(?string $value): ?Carbon
    {
        $value = trim((string) $value);

        if (! preg_match('/^(\d{4})-(\d{2})$/', $value, $m)) {
            return null;
        }

        [$year, $month] = [(int) $m[1], (int) $m[2]];

        if ($month < 1 || $month > 12) {
            return null;
        }

        return Carbon::create($year, $month, 1)->startOfMonth();
    }

    /**
     * `from`/`to` in, a sane window out: `from` defaults to 11 months before
     * `to`, `to` defaults to the current month, and an inverted pair is SWAPPED,
     * never rejected (§3.2).
     *
     * @return array{from: string, to: string, from_month: Carbon, to_month: Carbon, months: array<int, string>}
     */
    public function monthBounds(array $filters): array
    {
        $to = $this->parseMonth($filters['to'] ?? null) ?? now()->startOfMonth();
        $from = $this->parseMonth($filters['from'] ?? null)
            ?? $to->copy()->subMonths(self::SERIES_MONTHS - 1)->startOfMonth();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy(), $from->copy()];
        }

        return [
            'from'       => $from->format('Y-m'),
            'to'         => $to->format('Y-m'),
            'from_month' => $from,
            'to_month'   => $to,
            // The chart is a fixed 12 bars ending at `to`, whatever the range:
            // a 6-month filter must not draw a 6-bar chart with 12 labels.
            'months'     => $this->seriesMonths($to),
        ];
    }

    /** @return array<int, string> 12 `Y-m` labels, oldest first, ending at `$to`. */
    public function seriesMonths(Carbon $to): array
    {
        $months = [];

        for ($i = self::SERIES_MONTHS - 1; $i >= 0; $i--) {
            $months[] = $to->copy()->subMonths($i)->format('Y-m');
        }

        return $months;
    }

    // ── filters ──────────────────────────────────────────────────────

    /**
     * Request query -> validated filter array. Everything the user typed is
     * re-checked here; the view never trusts the string it rendered.
     */
    public function filters(array $query): array
    {
        $bounds = $this->monthBounds($query);

        $account = $this->stringOrNull($query['account'] ?? null);
        $status = $this->stringOrNull($query['status'] ?? null);

        return [
            'from'    => $bounds['from'],
            'to'      => $bounds['to'],
            'agency'  => $this->agencyFilter($query['agency'] ?? null),
            'account' => in_array($account, array_merge(Receivable::ACCOUNTS, [self::UNSPECIFIED_ACCOUNT]), true) ? $account : null,
            'status'  => in_array($status, ['received', 'pending', 'all'], true) ? $status : 'received',
            'q'       => mb_substr(trim((string) ($query['q'] ?? '')), 0, 100),
            'per'     => $this->perPage($query['per'] ?? null),
            'sort'    => in_array(($query['sort'] ?? ''), ['collected', 'outstanding', 'last'], true) ? $query['sort'] : 'collected',
            'dir'     => ($query['dir'] ?? '') === 'asc' ? 'asc' : 'desc',
        ];
    }

    /** `agency` = id, or `all`/blank for all agencies. */
    private function agencyFilter($value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    private function stringOrNull($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    public function perPage($requested): int
    {
        $per = (int) $requested;

        return in_array($per, self::PER_PAGE_OPTIONS, true) ? $per : 25;
    }

    // ── the one builder ──────────────────────────────────────────────

    /**
     * The filtered base builder. `withTrashed()` is the point: voided rows are
     * counted separately, not silently filtered out by the soft-delete scope.
     *
     * `agency_id` is a positive whitelist, not a whereIn negation, so `?agency=
     * 999999` is an EMPTY ledger rather than every tenant's money.
     */
    public function query(array $filters): Builder
    {
        $bounds = $this->monthBounds($filters);

        return Receivable::withTrashed()
            ->when(($filters['agency'] ?? null), fn (Builder $q) => $q->where('receivables.agency_id', (int) $filters['agency']))
            ->when(
                ($filters['account'] ?? null) === self::UNSPECIFIED_ACCOUNT,
                fn (Builder $q) => $q->whereNull('receivables.account')
            )
            ->when(
                filled($filters['account'] ?? null) && $filters['account'] !== self::UNSPECIFIED_ACCOUNT,
                fn (Builder $q) => $q->where('receivables.account', $filters['account'])
            )
            ->whereBetween('receivables.date', [
                $bounds['from_month']->toDateString(),
                $bounds['to_month']->copy()->endOfMonth()->toDateString(),
            ])
            ->when(filled($filters['q'] ?? null), fn (Builder $q) => $this->applySearch($q, (string) $filters['q']));
    }

    /**
     * `query()` narrowed by the `status` filter (B2 consistency).
     *
     * Everything the screen shows for one status — the KPIs, the monthly
     * series, the account mix and the per-agency table — has to be computed
     * from the SAME rows the table lists, or `status=pending` keeps a
     * "Collected" KPI above an outstanding-only table. So the narrowed
     * builder is opt-in: `query()` is untouched and the dashboard, which
     * always means the whole ledger, cannot drift.
     */
    public function narrowedQuery(array $filters): Builder
    {
        return $this->applyStatus($this->query($filters), $filters['status'] ?? 'received');
    }

    /**
     * `LIKE %q%` across ref_ar, particular, code and applicant name.
     *
     * `%` and `_` are escaped and the LIKE names an ESCAPE character: a lone
     * `%` must return zero rows, not the whole ledger (§6.14). MySQL's default
     * backslash escape is one `NO_BACKSLASH_ESCAPES` away from doing nothing,
     * so it is stated explicitly.
     *
     * The applicant join is a correlated EXISTS rather than a join, so a row
     * whose applicant was deleted still matches on its own columns and never
     * duplicates.
     */
    public function applySearch(Builder $query, string $term): Builder
    {
        $needle = '%'.$this->escapeLike($term).'%';

        return $query->where(function (Builder $q) use ($needle) {
            foreach (['ref_ar', 'particular', 'code'] as $column) {
                $q->orWhereRaw('receivables.'.$column.' LIKE ? ESCAPE ?', [$needle, '\\']);
            }

            $q->orWhereExists(function ($sub) use ($needle) {
                $sub->from('applicants')
                    ->whereColumn('applicants.id', 'receivables.applicant_id')
                    ->whereRaw(
                        "(applicants.first_name LIKE ? ESCAPE ? OR applicants.middle_name LIKE ? ESCAPE ? "
                        .'OR applicants.last_name LIKE ? ESCAPE ?)',
                        [$needle, '\\', $needle, '\\', $needle, '\\']
                    );
            });
        });
    }

    /** Escape the LIKE wildcards and the escape character itself. */
    public function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    // ── definitions ──────────────────────────────────────────────────

    /**
     * Collected = received and not deleted. Outstanding = pending and not
     * deleted. Everything else (cancelled, deleted) is voided.
     */
    private function collectedSql(): string
    {
        return "SUM(CASE WHEN receivables.status = 'received' AND receivables.deleted_at IS NULL THEN receivables.amount ELSE 0 END)";
    }

    private function outstandingSql(): string
    {
        return "SUM(CASE WHEN receivables.status = 'pending' AND receivables.deleted_at IS NULL THEN receivables.amount ELSE 0 END)";
    }

    private function voidedSql(): string
    {
        return "SUM(CASE WHEN receivables.status = 'cancelled' OR receivables.deleted_at IS NOT NULL THEN 1 ELSE 0 END)";
    }

    // ── summary ──────────────────────────────────────────────────────

    /**
     * Collected, outstanding, voided count, row count and agency count in ONE
     * query with conditional aggregates (§3.3.2).
     *
     * Two builders, one attempt at nothing: `money` is the status-narrowed
     * rows (B2 — the KPI counts what the table lists) and `window` is the
     * whole unfiltered window, used ONLY for `voided_count`. "1 voided" has
     * to stay true above a `status=received` table, which by definition
     * contains none of them. `row_count` and `agency_count` come from `money`.
     * `window` costs no query: `toBase()` keeps the bindings and the
     * where-clauses, so it joins the same statement.
     *
     * `window` compares the (unaliased) `id` against the window ids, because
     * bindings are positional and both subqueries carry the month bounds.
     *
     * @return array{collected: string, outstanding: string, voided_count: int, row_count: int, agency_count: int}
     */
    public function summary(array $filters): array
    {
        $key = md5(serialize($filters));

        if (isset($this->summaryCache[$key])) {
            return $this->summaryCache[$key];
        }

        $money = $this->narrowedQuery($filters);
        $window = $this->query($filters);

        $row = $money
            ->selectRaw($this->collectedSql().' as collected')
            ->selectRaw($this->outstandingSql().' as outstanding')
            ->selectRaw('COUNT(*) as row_count')
            ->selectRaw('COUNT(DISTINCT receivables.agency_id) as agency_count')
            ->selectRaw('(SELECT '.$this->voidedSql().' FROM receivables WHERE receivables.id IN ('.$window->toBase()->select('receivables.id')->toSql().')) as voided', $window->getBindings())
            ->first();

        return $this->summaryCache[$key] = [
            'collected'    => $this->decimal($row?->collected),
            'outstanding'  => $this->decimal($row?->outstanding),
            'voided_count' => (int) ($row?->voided ?? 0),
            'row_count'    => (int) ($row?->row_count ?? 0),
            'agency_count' => (int) ($row?->agency_count ?? 0),
        ];
    }

    /**
     * 12 `Y-m` buckets, zero-filled, in one grouped query.
     *
     * The bucket spans from the chart's oldest month to `to`, because a bar
     * with a hole is a bar an operator misreads. When the filter starts
     * earlier than the chart, the earlier months are still summed but fall
     * outside the returned map — which is what the KPI strip is for.
     *
     * @return array<string, string> `Y-m` => collected, zero as `0.00`
     */
    public function monthlySeries(array $filters): array
    {
        $bounds = $this->monthBounds($filters);
        $chart = $bounds['months'];
        $key = md5(serialize($filters));

        if (isset($this->seriesCache[$key])) {
            return $this->seriesCache[$key];
        }

        $series = array_fill_keys($chart, '0.00');

        // Query only the chart window, so `(agency_id,date)` is usable and the
        // numbers are the same ones the bars show.
        $chartFilters = $filters;
        $chartFilters['from'] = $chart[0];
        $chartFilters['to'] = $bounds['to'];

        $rows = $this->narrowedQuery($chartFilters)
            ->selectRaw($this->monthExpression().' as ym')
            ->selectRaw($this->collectedSql().' as collected')
            ->groupBy('ym')
            ->get();

        foreach ($rows as $row) {
            if (array_key_exists((string) $row->ym, $series)) {
                $series[(string) $row->ym] = $this->decimal($row->collected);
            }
        }

        return $this->seriesCache[$key] = $series;
    }

    /**
     * `strftime` on SQLite (tests), `DATE_FORMAT` on MySQL (production). Both
     * yield `Y-m`; the expression is chosen once, not guessed per row.
     */
    private function monthExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', receivables.date)"
            : "DATE_FORMAT(receivables.date, '%Y-%m')";
    }

    /**
     * Per-account collected, across every entry in `Receivable::ACCOUNTS`
     * plus `(unspecified)` for the NULL rows. Nulls are never dropped (§3.3.4).
     *
     * @return array<int, array{account: string, collected: string, outstanding: string}>
     */
    public function byAccount(array $filters): array
    {
        $rows = $this->narrowedQuery($filters)
            ->selectRaw('receivables.account as account')
            ->selectRaw($this->collectedSql().' as collected')
            ->selectRaw($this->outstandingSql().' as outstanding')
            ->groupBy('receivables.account')
            ->get()
            ->keyBy(fn ($row) => (string) ($row->account ?? self::UNSPECIFIED_ACCOUNT));

        $out = [];

        foreach (array_merge(Receivable::ACCOUNTS, [self::UNSPECIFIED_ACCOUNT]) as $account) {
            $row = $rows->get($account);

            $out[] = [
                'account'     => $account,
                'collected'   => $this->decimal($row?->collected),
                'outstanding' => $this->decimal($row?->outstanding),
            ];
        }

        return $out;
    }

    /**
     * One row per agency, INCLUDING agencies with no collections in the window
     * (§3.3.5). `₱0.00` is a real row an operator can click, not an absence.
     *
     * Built from `query()` — the SAME filtered builder as the summary — so
     * `sum(perAgency) == summary()['collected']` holds by construction, not by
     * two hand-kept copies of the filter list drifting apart (§4.3).
     *
     * One query: the aggregate is a grouped subquery, joined once to `agencies`.
     * No per-row loads.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function perAgency(array $filters)
    {
        // Status narrowed like the KPI above it, but `query()` on purpose:
        // `agency_count` must keep counting every agency in the window, or a
        // pending-only window would claim the other agencies paid nothing.
        $aggregates = $this->query($filters)
            ->tap(fn (Builder $q) => $this->applyStatus($q, $filters['status'] ?? 'received'))
            ->selectRaw('receivables.agency_id as agency_id')
            ->selectRaw($this->collectedSql().' as collected')
            ->selectRaw($this->outstandingSql().' as outstanding')
            ->selectRaw("MAX(CASE WHEN receivables.status = 'received' AND receivables.deleted_at IS NULL THEN receivables.date END) as last_payment_date")
            ->selectRaw("SUM(CASE WHEN receivables.status = 'received' AND receivables.deleted_at IS NULL THEN 1 ELSE 0 END) as received_count")
            ->groupBy('receivables.agency_id');

        $query = DB::table('agencies')
            ->leftJoinSub($aggregates, 'receivable_totals', fn ($join) => $join->on('receivable_totals.agency_id', '=', 'agencies.id'))
            ->select('agencies.id', 'agencies.name', 'agencies.status', 'agencies.subdomain')
            ->selectRaw('COALESCE(receivable_totals.collected, 0) as collected')
            ->selectRaw('COALESCE(receivable_totals.outstanding, 0) as outstanding')
            ->selectRaw('receivable_totals.last_payment_date as last_payment_date')
            ->selectRaw('COALESCE(receivable_totals.received_count, 0) as received_count')
            ->when(($filters['agency'] ?? null), fn ($q) => $q->where('agencies.id', (int) $filters['agency']));

        $dir = ($filters['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        match ($filters['sort'] ?? 'collected') {
            'outstanding' => $query->orderBy('outstanding', $dir),
            'last'        => $query->orderBy('receivable_totals.last_payment_date', $dir),
            default       => $query->orderBy('collected', $dir),
        };

        return $query->orderBy('agencies.name')->get()->map(fn ($row) => (object) [
            'id'                => (int) $row->id,
            'name'              => (string) $row->name,
            'status'            => (string) $row->status,
            'subdomain'         => $row->subdomain,
            'collected'         => $this->decimal($row->collected),
            'outstanding'       => $this->decimal($row->outstanding),
            'last_payment_date' => $row->last_payment_date,
            'received_count'    => (int) $row->received_count,
        ]);
    }

    /**
     * Outstanding (pending, not deleted) money by age of the row's `date`:
     * 0-30, 31-60, 61-90, 90+ days, plus a "future" bucket for a dated-ahead
     * due, which is not aged and must not be hidden.
     *
     * SQLite (tests) and MySQL (production) both support julianday/DATEDIFF
     * differently, so the difference is expressed once here.
     *
     * The bucket is computed in SQL, by the same CASE the display labels use,
     * and summed once per bucket — four rows, not one row per receivable.
     * The result strings are converted to decimal ONCE, here. They must never
     * be re-converted on the way out: the previous version fed an already
     * decimal bucket string back through `toCents()`, which threw on the
     * first row (500 on the default window) and would have reported every
     * amount 100x too large if the cast had been loose (B1).
     *
     */
    public function ageing(array $filters): array
    {
        $rows = $this->query($filters)
            ->whereNull('receivables.deleted_at')
            ->where('receivables.status', Receivable::STATUS_PENDING)
            ->toBase()
            ->selectRaw($this->ageBucketExpression().' as bucket')
            ->selectRaw('COUNT(*) as row_count')
            ->selectRaw('SUM(receivables.amount) as amount')
            ->groupBy('bucket')
            ->get()
            ->keyBy(fn ($row) => (string) $row->bucket);

        $out = [];
        foreach (self::AGEING_BUCKETS as $bucket) {
            $row = $rows->get($bucket);

            $out[$bucket] = [
                'count'  => (int) ($row?->row_count ?? 0),
                'amount' => $this->decimal($row?->amount),
            ];
        }

        return $out;
    }

    private function ageDaysExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(julianday('now') - julianday(date(receivables.date)) AS INTEGER)"
            : 'DATEDIFF(CURDATE(), receivables.date)';
    }

    /**
     * The bucket label for one row, as server-side SQL, so every driver
     * groups on the SAME definition of "90+ days".
     */
    private function ageBucketExpression(): string
    {
        $days = $this->ageDaysExpression();

        return "CASE WHEN {$days} <= 30 THEN '0-30'
                     WHEN {$days} <= 60 THEN '31-60'
                     WHEN {$days} <= 90 THEN '61-90'
                     ELSE '90+' END";
    }

    // ── pages ────────────────────────────────────────────────────────

    /**
     * The ledger table: one row per receivable in the window, with its agency
     * name joined once (no per-row `->agency`), paginated 25/50/100.
     *
     * `status` follows the summary's definitions, not a raw column, and means
     * what the heading above it says (B2): `received` = collected, `pending` =
     * outstanding, `all` = everything including voided rows. `pending` used to
     * show received rows under an Outstanding heading.
     */
    public function receivablePage(array $filters, ?int $agencyId = null): LengthAwarePaginator
    {
        // One builder: the rows, the KPIs and the per-agency table all come
        // from `narrowedQuery()`, so they cannot disagree (B2, §4.3).
        return $this->narrowedQuery($this->forAgency($filters, $agencyId))
            ->join('agencies', 'agencies.id', '=', 'receivables.agency_id')
            ->select('receivables.*', 'agencies.name as agency_name')
            ->orderByDesc('receivables.date')
            ->orderByDesc('receivables.id')
            ->paginate($this->perPage($filters['per'] ?? null))
            ->withQueryString();
    }

    /** The same filters, narrowed to one agency. */
    private function forAgency(array $filters, ?int $agencyId): array
    {
        if ($agencyId !== null) {
            $filters['agency'] = $agencyId;
        }

        return $filters;
    }

    /**
     * The status filter as ONE definition, in the terms the summary already
     * uses: `received` = collected, `pending` = outstanding, `all` = every row
     * including voided. Anything else falls back to collected.
     *
     * Public because the ledger's per-agency table is ONE query (aggregate
     * subquery joined to `agencies`), so it cannot go through
     * `narrowedQuery()` without losing the zero-collection agencies.
     */
    public function applyStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            'pending' => $query->where('receivables.status', Receivable::STATUS_PENDING)
                ->whereNull('receivables.deleted_at'),
            'all' => $query,
            default => $query->where('receivables.status', Receivable::STATUS_RECEIVED)
                ->whereNull('receivables.deleted_at'),
        };
    }

    /**
     * Voided rows for the drill-in: cancelled or soft-deleted, in the window,
     * newest first, capped at 25. Shown with a badge, never summed (§4.4).
     * Deliberately not paginated: the count is reported next to it, and a
     * second paginator on one page confuses `?page=`.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function voidedPage(array $filters, ?int $agencyId = null)
    {
        return $this->query($this->forAgency($filters, $agencyId))
            ->join('agencies', 'agencies.id', '=', 'receivables.agency_id')
            ->select('receivables.*', 'agencies.name as agency_name')
            ->where(function (Builder $q) {
                $q->where('receivables.status', Receivable::STATUS_CANCELLED)
                    ->orWhereNotNull('receivables.deleted_at');
            })
            ->orderByDesc('receivables.date')
            ->orderByDesc('receivables.id')
            ->limit(25)
            ->get();
    }

    /**
     * The drill-in page: the rows for one agency that match `status`, most
     * recent first, with the applicant name joined only here (never on the
     * aggregate screens, §4.5).
     */
    public function agencyPage(array $filters, int $agencyId): LengthAwarePaginator
    {
        // Status-narrowed like the ledger's table (B2). `agencyPage()` is only
        // reached from `show()`, so this changes no dashboard number.
        return $this->narrowedQuery($this->forAgency($filters, $agencyId))
            ->leftJoin('applicants', 'applicants.id', '=', 'receivables.applicant_id')
            ->select('receivables.*', 'applicants.first_name', 'applicants.middle_name', 'applicants.last_name')
            ->orderByDesc('receivables.date')
            ->orderByDesc('receivables.id')
            ->paginate($this->perPage($filters['per'] ?? null))
            ->withQueryString();
    }

    // ── dashboard ────────────────────────────────────────────────────

    /**
     * Everything `/godmode` shows, from the SAME methods as `/godmode/payments`
     * (§2). Two windows: the current month for the KPI strip, the rolling 12
     * for the tile and the chart.
     *
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $thisMonth = $this->filters(['from' => now()->format('Y-m'), 'to' => now()->format('Y-m')]);
        $rolling = $this->filters([]);

        return [
            'thisMonth'   => $thisMonth,
            'rolling'     => $rolling,
            'summaryThis' => $this->summary($thisMonth),
            'summaryRolling' => $this->summary($rolling),
            'series'      => $this->monthlySeries($rolling),
            'months'      => $this->monthBounds($rolling)['months'],
            'top'         => $this->perAgency(array_merge($rolling, ['sort' => 'collected', 'dir' => 'desc', 'agency' => null]))
                ->take(self::TOP_AGENCIES),
        ];
    }

    // ── money ────────────────────────────────────────────────────────

    /**
     * `₱1,234,567.89`. Two decimals always, `₱0.00` for zero. Formatted from
     * the decimal STRING the database returned, not from a PHP float (§3.3.7).
     */
    public function formatMoney($value): string
    {
        return '₱'.number_format($this->toCents($value) / 100, 2);
    }

    /**
     * Decimal (string|null) -> integer centavos. The only money arithmetic the
     * service does in PHP, and it is exact: string split, no float multiply.
     */
    public function toCents($value): int
    {
        $string = trim((string) ($value ?? '0'));

        $negative = str_starts_with($string, '-');
        $string = ltrim($string, '+-');

        $parts = explode('.', $string, 2);
        $whole = $parts[0] === '' ? '0' : $parts[0];
        $fraction = str_pad(substr($parts[1] ?? '', 0, 2), 2, '0');

        $cents = ((int) $whole) * 100 + (int) $fraction;

        return $negative ? -$cents : $cents;
    }

    /** SQL aggregate (string|null) -> normalised `0.00`-style decimal string. */
    public function decimal($value): string
    {
        $cents = $this->toCents($value);

        return sprintf('%s%d.%02d', $cents < 0 ? '-' : '', intdiv(abs($cents), 100), abs($cents) % 100);
    }

    /** Bar height as a percentage of the tallest bar; 0 when everything is zero. */
    public function barHeight(string $amount, string $max): float
    {
        $maxCents = $this->toCents($max);
        $cents = $this->toCents($amount);

        if ($maxCents <= 0 || $cents <= 0) {
            return 0.0;
        }

        return round(max(2.0, ($cents / $maxCents) * 100), 2);
    }

    // ── agencies for the filter ──────────────────────────────────────

    /** All agencies, id => name, one query, for the filter dropdown. */
    public function agencies(): array
    {
        return Agency::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    public function agency(int $id): ?Agency
    {
        return $this->agencyCache[$id] ??= Agency::query()->find($id);
    }

    /** Field-set the drill-in header shows. No money, no edit. */
    public function agencyHeader(Agency $agency): array
    {
        return [
            'id'        => $agency->id,
            'name'      => $agency->name,
            'status'    => $agency->status,
            'subdomain' => $agency->subdomain,
        ];
    }
}
