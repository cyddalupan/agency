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
            'total_paid'          => $this->money($paidInitial + $paidMonthly),
            'total_contract'      => $plan ? (string) $plan->total_contract_value : '0.00',
            'payments'            => $payments,
            'payment_count'       => $payments->count(),
        ];
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
