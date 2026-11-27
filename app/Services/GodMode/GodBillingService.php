<?php

declare(strict_types=1);

namespace App\Services\GodMode;

use App\Models\Agency;
use App\Models\AgencyBillingPayment;
use App\Models\AgencyBillingPlan;

/**
 * Card 6b07eb557762396b008a7080 — the arithmetic behind "what has this agency
 * paid Landas, and what is still owed".
 *
 * Everything here is derived from `agency_billing_plans` and
 * `agency_billing_payments`. It NEVER reads `receivables`: that table is
 * agency-internal money (applicant/agent -> agency), which is a different
 * question, and the whole point of the village payments work is that the old
 * screen answered the wrong one.
 *
 * Deliberately absent: overdue/late detection. Cyd deferred it — *"we will deal
 * with overdue in the future"* — and the status engine card owns it. Do not add
 * a due date, a lateness flag or a "days overdue" number here.
 *
 * Voided payments are invisible to every total because `AgencyBillingPayment`
 * soft-deletes; the default query already excludes them, and that is the whole
 * of "void removes it from the totals".
 */
class GodBillingService
{
    /** The agency's current plan: active, newest first. Null when none exists. */
    public function activePlan(Agency $agency): ?AgencyBillingPlan
    {
        return AgencyBillingPlan::query()
            ->where('agency_id', $agency->id)
            ->where('is_active', true)
            ->orderByDesc('starts_on')
            ->orderByDesc('id')
            ->first();
    }

    /** The agency's plans, newest first — the modal lists them. */
    public function plans(Agency $agency): \Illuminate\Support\Collection
    {
        return AgencyBillingPlan::query()
            ->where('agency_id', $agency->id)
            ->orderByDesc('is_active')
            ->orderByDesc('starts_on')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Card 6b07f35ccd9659edc7b8438e — supersede the agreement.
     *
     * The old plan is DEACTIVATED and a NEW row is inserted; the old row is
     * never mutated. Two reasons, both load-bearing:
     *
     *  1. Payments keep their `plan_id`. If the plan row were edited in place,
     *     a ₱50,000 payment recorded under a now-₱60,000 agreement would appear
     *     to have been paid against the new terms — a quiet rewrite of history.
     *  2. "Who changed the price, and from what" stays answerable by reading
     *     the rows rather than a single mutable record.
     *
     * There must always be exactly one active plan, so the swap happens in one
     * transaction: the old row cannot be left deactivated with nothing to
     * replace it if the insert fails.
     *
     * @param  array<string,mixed>  $input  validated: initial_payment,
     *         initial_terms, monthly_amount, terms, starts_on, notes
     * @return array{old:?AgencyBillingPlan,new:AgencyBillingPlan}
     */
    public function supersedePlan(Agency $agency, array $input): array
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($agency, $input) {
            $old = $this->activePlan($agency);

            if ($old !== null) {
                $old->forceFill(['is_active' => false])->save();
            }

            $new = AgencyBillingPlan::create([
                'agency_id'       => $agency->id,
                'initial_payment' => number_format((float) $input['initial_payment'], 2, '.', ''),
                'initial_terms'   => (int) $input['initial_terms'],
                'monthly_amount'  => number_format((float) $input['monthly_amount'], 2, '.', ''),
                'terms'           => (int) $input['terms'],
                'currency'        => $old?->currency ?? 'PHP',
                'starts_on'       => $input['starts_on'],
                'notes'           => $input['notes'] ?? null,
                'is_active'       => true,
            ]);

            return ['old' => $old, 'new' => $new];
        });
    }

    /**
     * How many initial payments are recorded, and what they add up to.
     *
     * AC3 of this card is explicit that the COUNT is what drives "n of N
     * initial payments", not the money: two ₱12,500 payments against a ₱50,000
     * initial split four ways is "2 of 4", even though it is only half the
     * cash. A partial payment therefore does NOT advance the count.
     */
    public function initialProgress(Agency $agency): array
    {
        $rows = AgencyBillingPayment::query()
            ->where('agency_id', $agency->id)
            ->where('payment_type', AgencyBillingPayment::TYPE_INITIAL)
            ->get(['amount']);

        return [
            'count' => $rows->count(),
            'paid'  => $this->money((float) $rows->sum(fn ($r) => (float) $r->amount)),
        ];
    }

    /**
     * The billing summary the modal shows and the amount pre-fill reads.
     *
     * `initial_outstanding` and `monthly_outstanding` are what the operator
     * sees before typing; neither is clamped at zero, because an agency that
     * overpaid has genuinely overpaid and hiding the negative would be a lie.
     */
    public function summary(Agency $agency): array
    {
        $plan = $this->activePlan($agency);

        $paidInitial = $this->paidTotal($agency, AgencyBillingPayment::TYPE_INITIAL);
        $paidMonthly = $this->paidTotal($agency, AgencyBillingPayment::TYPE_MONTHLY);

        $initialDue = $plan ? (float) $plan->initial_payment : 0.0;
        $monthlyDue = $plan ? (float) $plan->monthly_amount * (int) $plan->terms : 0.0;

        $payments = $this->payments($agency);

        // ── Card 6b07f359a6a548392171d9de: the modal needs the STATUS ENGINE's
        // answers, not a second arithmetic. VillageBillingState already owns
        // the schedule rules (anchor = first payment, retainer starts at
        // cleared_on + 1 month, zero grace). Re-deriving "next due" here would
        // eventually disagree with the building's colour, and an operator
        // reading both would stop trusting either.
        $state = $this->state($agency, $plan, $paidInitial, $paidMonthly, $payments);

        $totalContract = $plan ? (float) $plan->total_contract_value : 0.0;
        $totalPaid = $paidInitial + $paidMonthly;

        return [
            'has_plan'            => $plan !== null,
            'plan_id'             => $plan?->id,
            'plan'                => $plan,
            'initial_due'         => $this->money($initialDue),
            'initial_paid'        => $this->money($paidInitial),
            'initial_outstanding' => $this->money($initialDue - $paidInitial),
            'initial_terms'       => $plan ? (int) $plan->initial_terms : 0,
            'initial_count'       => $this->paidCount($agency, AgencyBillingPayment::TYPE_INITIAL),
            'monthly_due'         => $this->money($monthlyDue),
            'monthly_amount'      => $plan ? (string) $plan->monthly_amount : '0.00',
            'monthly_paid'        => $this->money($paidMonthly),
            'monthly_outstanding' => $this->money($monthlyDue - $paidMonthly),
            'monthly_terms'       => $plan ? (int) $plan->terms : 0,
            'monthly_count'       => $this->paidCount($agency, AgencyBillingPayment::TYPE_MONTHLY),
            'total_paid'          => $this->money($totalPaid),
            'total_contract'      => $plan ? (string) $plan->total_contract_value : '0.00',
            // AC5: NOT clamped. An overpaid agency has a negative balance and
            // the operator should see that, not a friendly zero.
            'balance'             => $this->money($totalContract - $totalPaid),
            'payments'            => $payments,
            'payment_count'       => $payments->count(),
            // AC2: the progress line reads "₱X of ₱Y paid — n of N initial
            // payments". `paid` is money, `count` is rows: a partial payment
            // raises the money and not the count, deliberately.
            'initial_progress'    => $this->initialProgress($agency),
        ] + $state;
    }

    /**
     * The two-axis status plus schedule dates, from VillageBillingState.
     *
     * @return array<string,mixed>
     */
    private function state(
        Agency $agency,
        ?AgencyBillingPlan $plan,
        float $paidInitial,
        float $paidMonthly,
        \Illuminate\Support\Collection $payments
    ): array {
        $initialCount = 0;
        $monthlyCount = 0;
        $payArray = [];

        foreach ($payments as $p) {
            $payArray[] = [
                'paid_on'      => (string) $p->paid_on,
                'amount'       => (string) $p->amount,
                'payment_type' => (string) $p->payment_type,
            ];
            if ($p->payment_type === AgencyBillingPayment::TYPE_INITIAL) { $initialCount++; }
            elseif ($p->payment_type === AgencyBillingPayment::TYPE_MONTHLY) { $monthlyCount++; }
        }

        $axes = (new VillageBillingState(
            $plan ? [
                'initial_payment' => (string) $plan->initial_payment,
                'initial_terms'   => (int) $plan->initial_terms,
                'monthly_amount'  => (string) $plan->monthly_amount,
                'terms'           => (int) $plan->terms,
                'starts_on'       => $plan->starts_on?->toDateString(),
            ] : null,
            [
                'initial_paid'  => number_format($paidInitial, 2, '.', ''),
                'initial_count' => $initialCount,
                'monthly_paid'  => number_format($paidMonthly, 2, '.', ''),
                'monthly_count' => $monthlyCount,
            ],
            $payArray,
            \Carbon\CarbonImmutable::now()->startOfDay()
        ))->axes();

        return [
            'initial_state'        => $axes['initial_state'],
            'monthly_state'        => $axes['monthly_state'],
            'initial_cleared'      => $axes['initial_cleared'],
            'initial_cleared_on'   => $axes['initial_cleared_on'],
            'initial_due_on'       => $axes['initial_due_on'],
            'monthly_due_on'       => $axes['monthly_due_on'],
            'monthly_expected'     => $axes['monthly_expected'],
            'late_initial'         => $axes['late_initial'],
            'late_monthly'         => $axes['late_monthly'],
            // The first payment on the plan is the schedule anchor — the
            // operator needs to see the date the whole arithmetic hangs on.
            'anchor_on'            => $this->firstPaymentOn($payments),
        ];
    }

    /**
     * Earliest payment date, which is the instalment anchor. Null when the
     * agency has never paid anything — there is no schedule yet.
     */
    private function firstPaymentOn(\Illuminate\Support\Collection $payments): ?string
    {
        $first = $payments
            ->map(fn ($p) => $p->paid_on ? \Carbon\CarbonImmutable::parse((string) $p->paid_on) : null)
            ->filter()
            ->sort()
            ->first();

        return $first?->toDateString();
    }

    /** Live (non-voided) payments, newest first. */
    public function payments(Agency $agency): \Illuminate\Support\Collection
    {
        return AgencyBillingPayment::query()
            ->with('recorder:id,name')
            ->where('agency_id', $agency->id)
            ->orderByDesc('paid_on')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * What is still owed for one payment type — the pre-fill value.
     *
     * INITIAL is cumulative: the initial payment is cleared by `initial_terms`
     * payments, so "what is left" is the real number.
     *
     * MONTHLY is NOT cumulative for the purpose of a single payment. The card
     * is explicit: the amount box pre-fills with `monthly_amount`, one month's
     * instalment. Pre-filling with the whole contract value (amount x terms)
     * put 18,000 in the box for a 1,500/month agency and invited exactly the
     * wrong entry — caught in QA on 2026-11-27.
     */
    public function outstanding(Agency $agency, string $type): string
    {
        $summary = $this->summary($agency);

        return $type === AgencyBillingPayment::TYPE_INITIAL
            ? $summary['initial_outstanding']
            : $summary['monthly_amount'];
    }

    private function paidTotal(Agency $agency, string $type): float
    {
        return (float) AgencyBillingPayment::query()
            ->where('agency_id', $agency->id)
            ->where('payment_type', $type)
            ->sum('amount');
    }

    private function paidCount(Agency $agency, string $type): int
    {
        return (int) AgencyBillingPayment::query()
            ->where('agency_id', $agency->id)
            ->where('payment_type', $type)
            ->count();
    }

    /** Strings end to end: these are money, and floats are how money drifts. */
    private function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
