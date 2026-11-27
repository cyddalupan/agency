<?php

declare(strict_types=1);

namespace App\Services\GodMode;

use App\Models\Agency;
use App\Models\AgencyBillingPayment;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Card 6b07f3667e9a74a10e2fe55f — the complete payment ledger for ONE agency.
 *
 * The agency modal's billing summary shows the last few rows and a total; that
 * is the right size for a modal, but an operator reconciling a bank statement
 * needs everything ("show me everything this agency paid us"). This service
 * reads `agency_billing_payments` — the same table `GodBillingService` writes —
 * and never `receivables`, which is agency-internal money and answers a
 * different question entirely.
 *
 * READ-ONLY. There is no create, update or delete here, and there must never
 * be one: recording and voiding belong to the record-payment card, which lives
 * in the modal. AC8 asserts that this card adds no POST route.
 *
 * Totals are computed from the SAME rows the table renders (the filtered set is
 * the source for both), so a footer can never disagree with the page above it.
 */
class PaymentLedgerService
{
    public const PER_PAGE_OPTIONS = [25, 50, 100];

    public const TYPE_ALL = 'all';

    /** Type filter accepted values. */
    public const TYPE_FILTERS = [self::TYPE_ALL, AgencyBillingPayment::TYPE_INITIAL, AgencyBillingPayment::TYPE_MONTHLY];

    /**
     * Request query -> validated filter array, console style: the view never
     * trusts the string it rendered, so everything is re-checked here.
     *
     * `from`/`to` stay as `Y-m-d` or null — this ledger is dated by `paid_on`,
     * a concrete day, rather than by month like the receivables dashboard.
     *
     * @return array{type:string, voided:bool, from:?string, to:?string, per:int}
     */
    public function filters(array $query): array
    {
        $type = (string) ($query['type'] ?? '');
        $from = $this->dateOrNull($query['from'] ?? null);
        $to = $this->dateOrNull($query['to'] ?? null);

        // A backwards range is a typo, not an error page: swap it so the
        // operator still sees the window they meant.
        if ($from !== null && $to !== null && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [
            'type'   => in_array($type, self::TYPE_FILTERS, true) ? $type : self::TYPE_ALL,
            // AC3: voided rows are hidden unless the operator asks for them.
            // A checkbox posts `1`/`on` when ticked and nothing when not, so
            // presence of a truthy value is the test — not equality to '0'.
            'voided' => $this->truthy($query['voided'] ?? null),
            'from'   => $from,
            'to'     => $to,
            'per'    => $this->perPage($query['per'] ?? null),
        ];
    }

    /**
     * The ledger page, newest first (AC2).
     *
     * AC3: with `voided` off, only live rows — `AgencyBillingPayment` soft
     * deletes, so the default query already excludes them. With it on, the
     * trashed rows join the same ordering rather than being appended at the
     * bottom, because a voided payment still happened on its `paid_on` date and
     * hiding it out of sequence would misrepresent the account's history.
     */
    public function page(Agency $agency, array $filters): LengthAwarePaginator
    {
        $query = AgencyBillingPayment::query()
            ->with(['recorder:id,name', 'plan:id,initial_payment,initial_terms,monthly_amount,terms'])
            ->where('agency_id', $agency->id)
            ->orderByDesc('paid_on')
            ->orderByDesc('id');

        if ($filters['voided']) {
            $query->withTrashed();
        }

        if ($filters['type'] !== self::TYPE_ALL) {
            $query->where('payment_type', $filters['type']);
        }

        if ($filters['from'] !== null) {
            $query->whereDate('paid_on', '>=', $filters['from']);
        }

        if ($filters['to'] !== null) {
            $query->whereDate('paid_on', '<=', $filters['to']);
        }

        return $query->paginate($filters['per'])->withQueryString();
    }

    /**
     * Footer totals for the SAME filtered set the page renders.
     *
     * AC4: `active` here must equal the modal summary's `total_paid` for the
     * same agency once the filters are cleared. Both sum live rows of
     * `agency_billing_payments` for that agency, so they agree by construction —
     * the reconciliation test seeds rows and compares the two numbers.
     *
     * Totals are computed over the whole filtered set, not just the visible
     * page: a footer that changed when you paginated would not be a total.
     *
     * @return array{active:float, voided:float, net:float, active_count:int, voided_count:int}
     */
    public function totals(Agency $agency, array $filters): array
    {
        $rows = $this->filteredAmounts($agency, $filters);

        $active = 0.0;
        $voided = 0.0;
        $activeCount = 0;
        $voidedCount = 0;

        foreach ($rows as $row) {
            $amount = (float) $row->amount;

            if ($row->deleted_at !== null) {
                $voided += $amount;
                $voidedCount++;
            } else {
                $active += $amount;
                $activeCount++;
            }
        }

        return [
            'active'       => round($active, 2),
            'voided'       => round($voided, 2),
            'net'          => round($active - $voided, 2),
            'active_count' => $activeCount,
            'voided_count' => $voidedCount,
        ];
    }

    /**
     * Void attribution.
     *
     * AC §4 asks for `voided_by` / `voided_at` "if those columns exist; if not,
     * note that attribution requires them and raise it rather than silently
     * omitting." They do NOT exist on `agency_billing_payments`: voiding is a
     * soft delete (`deleted_at`) and the actor is written to the audit row as
     * `billing_payment_voided` -> `voided_by` (see GodBillingController::void).
     *
     * So the table can show WHEN a row was voided (`deleted_at`, free) but not
     * WHO from the row itself. This method surfaces the actor from the audit
     * trail instead of dropping the column, and the view labels the source, so
     * nobody assumes it came from the payment row.
     *
     * @return array<int, array{by:?string, at:?string}>
     */
    public function voidAudit(Agency $agency, iterable $payments): array
    {
        $ids = [];
        foreach ($payments as $payment) {
            if ($payment->deleted_at !== null) {
                $ids[] = (int) $payment->id;
            }
        }

        if ($ids === []) {
            return [];
        }

        // Voiding writes an `audit_events` row through
        // GodAgencyService::writeAudit: action `billing_payment_voided`, the
        // actor in `actor_user_id`, and the detail in the `after` JSON —
        // including `payment_id` and `voided_by`. That is where attribution
        // lives; the payment row itself only carries `deleted_at`.
        $events = \App\Models\AuditEvent::withoutGlobalScopes()
            ->with('actor:id,name')
            ->where('agency_id', $agency->id)
            ->where('action', 'billing_payment_voided')
            ->orderBy('id')
            ->get();

        $out = [];

        foreach ($events as $event) {
            $payload = $event->after;

            if (is_string($payload)) {
                $payload = json_decode($payload, true);
            }

            if (! is_array($payload)) {
                continue;
            }

            $paymentId = (int) ($payload['payment_id'] ?? 0);

            // Only rows on this page, and only rows actually voided — a stale
            // audit row must not invent a status for a live payment.
            if ($paymentId <= 0 || ! in_array($paymentId, $ids, true)) {
                continue;
            }

            // Oldest wins: the first void of a row is its attribution.
            if (! isset($out[$paymentId])) {
                $out[$paymentId] = [
                    'by' => $event->actor?->name
                        ?? (isset($payload['voided_by']) && $payload['voided_by'] !== null
                            ? 'User #'.$payload['voided_by']
                            : null),
                    'at' => $event->created_at?->toDateTimeString(),
                ];
            }
        }

        return $out;
    }

    /**
     * Does this agency have any billing history at all? Drives the empty state
     * (AC7) — "no plan and no payments" reads differently from "a plan but
     * nothing paid yet", and an operator needs to know which one they are in.
     */
    public function hasAnyPayment(Agency $agency): bool
    {
        return AgencyBillingPayment::withTrashed()
            ->where('agency_id', $agency->id)
            ->exists();
    }

    /** Mode badge colour, kept next to the data so the view stays dumb. */
    public function modeBadge(string $mode): string
    {
        return match ($mode) {
            'Cash'          => 'badge-success',
            'Cheque'        => 'badge-info',
            'Fund Transfer' => 'badge-primary',
            'GCash'         => 'badge-secondary',
            'Credit'        => 'badge-warning',
            default         => 'badge-ghost',
        };
    }

    /** @return \Illuminate\Support\Collection<int, object> */
    private function filteredAmounts(Agency $agency, array $filters): \Illuminate\Support\Collection
    {
        $query = AgencyBillingPayment::query()
            ->where('agency_id', $agency->id)
            ->select(['id', 'amount', 'deleted_at']);

        if ($filters['voided']) {
            $query->withTrashed();
        }

        if ($filters['type'] !== self::TYPE_ALL) {
            $query->where('payment_type', $filters['type']);
        }

        if ($filters['from'] !== null) {
            $query->whereDate('paid_on', '>=', $filters['from']);
        }

        if ($filters['to'] !== null) {
            $query->whereDate('paid_on', '<=', $filters['to']);
        }

        return collect($query->get());
    }

    private function dateOrNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function truthy(mixed $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'on', 'true', 'yes'], true);
    }

    private function perPage(mixed $value): int
    {
        $per = (int) $value;

        return in_array($per, self::PER_PAGE_OPTIONS, true) ? $per : 25;
    }
}
