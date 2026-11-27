<?php

declare(strict_types=1);

namespace App\Http\Controllers\GodMode;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\AgencyBillingPayment;
use App\Models\Receivable;
use App\Services\GodMode\GodAgencyService;
use App\Services\GodMode\GodBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Card 6b07eb557762396b008a7080 — recording what an agency paid Landas, from
 * the village modal.
 *
 * This writes `agency_billing_payments`, Landas's own ledger. It deliberately
 * does NOT touch `receivables`, which is agency-internal money and stays
 * read-only from the console forever (see the route comment in routes/web.php).
 *
 * Void, never edit. Correcting a payment means voiding it and recording a new
 * one, so `recorded_by` and the audit trail stay honest. There is no update
 * action here and there should not be one.
 */
class GodBillingController extends Controller
{
    public function __construct(
        private readonly GodBillingService $billing,
        private readonly GodAgencyService $agencies,
    ) {
    }

    /** The summary the modal renders — one GET, no writes. */
    public function summary(Agency $agency): JsonResponse
    {
        return response()->json(['ok' => true] + $this->billing->summary($agency));
    }

    public function store(Request $request, Agency $agency): JsonResponse
    {
        $validated = $request->validate([
            'payment_type' => ['required', Rule::in(AgencyBillingPayment::TYPES)],
            'amount'       => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'paid_on'      => ['required', 'date', 'before_or_equal:'.now()->addDay()->toDateString()],
            'mode'         => ['required', Rule::in(Receivable::MODES)],
            'reference'    => ['nullable', 'string', 'max:255'],
            'particular'   => ['nullable', 'string', 'max:255'],
        ]);

        $plan = $this->billing->activePlan($agency);

        // `recorded_by` and `agency_id` are NEVER read from the form: the first
        // is who actually clicked, the second is the route's agency. A forged
        // id in the payload is simply not in the validated set.
        $before = $this->billing->summary($agency);

        $payment = AgencyBillingPayment::create([
            'agency_id'    => $agency->id,
            'plan_id'      => $plan?->id,
            'payment_type' => $validated['payment_type'],
            'amount'       => number_format((float) $validated['amount'], 2, '.', ''),
            'paid_on'      => $validated['paid_on'],
            'mode'         => $validated['mode'],
            'reference'    => $validated['reference'] ?? null,
            'particular'   => $validated['particular'] ?? null,
            'recorded_by'  => $request->user('godmode')?->id,
        ]);

        $after = $this->billing->summary($agency);

        $this->audit(
            $agency,
            'billing_payment_recorded',
            $request,
            [
                'payment_id'      => $payment->id,
                'payment_type'    => $payment->payment_type,
                'amount'          => (string) $payment->amount,
                'paid_on'         => $payment->paid_on?->toDateString(),
                'plan_id'         => $payment->plan_id,
                'recorded_by'     => $payment->recorded_by,
                'initial_paid'    => $after['initial_paid'],
                'monthly_paid'    => $after['monthly_paid'],
                'total_paid'      => $after['total_paid'],
                'initial_before'  => $before['initial_paid'],
                'monthly_before'  => $before['monthly_paid'],
            ],
            $after
        );

        return response()->json([
            'ok'      => true,
            'payment' => [
                'id'           => $payment->id,
                'payment_type' => $payment->payment_type,
                'amount'       => (string) $payment->amount,
            ],
            'message' => ucfirst($payment->payment_type).' payment recorded.',
        ], 201);
    }

    public function void(Request $request, Agency $agency, AgencyBillingPayment $payment): JsonResponse
    {
        // Route-bound ids are not scoped to each other, so check the pairing
        // rather than trusting the URL.
        abort_unless((int) $payment->agency_id === (int) $agency->id, 404);

        $before = $this->billing->summary($agency);

        // Soft delete only. A hard delete destroys the audit story.
        $payment->delete();

        $after = $this->billing->summary($agency);

        $this->audit(
            $agency,
            'billing_payment_voided',
            $request,
            [
                'payment_id'     => $payment->id,
                'payment_type'   => $payment->payment_type,
                'amount'         => (string) $payment->amount,
                'paid_on'        => $payment->paid_on?->toDateString(),
                'recorded_by'    => $payment->recorded_by,
                'voided_by'      => $request->user('godmode')?->id,
                'initial_before' => $before['initial_paid'],
                'monthly_before' => $before['monthly_paid'],
                'total_before'   => $before['total_paid'],
            ],
            $after
        );

        return response()->json([
            'ok'      => true,
            'message' => 'Payment voided.',
        ]);
    }

    /**
     * Uses the existing god-plane audit helper — `GodAgencyService::writeAudit`
     * — rather than a second audit path. `after` carries the resulting totals
     * so the money state at the moment of the write is recoverable from the
     * audit row alone.
     */
    /**
     * Card 6b07f35ccd9659edc7b8438e — change the commercial agreement.
     *
     * Supersedes rather than edits (see GodBillingService::supersedePlan). The
     * confirm step lives in the modal: when the agency already has payments the
     * operator must tick a box acknowledging that past payments will not move,
     * and this action re-checks that here rather than trusting the browser.
     */
    public function updatePlan(Request $request, Agency $agency): JsonResponse
    {
        $validated = $request->validate([
            'initial_payment' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'initial_terms'   => ['required', 'integer', 'min:1', 'max:120'],
            'monthly_amount'  => ['required', 'numeric', 'min:0', 'max:999999999'],
            'terms'           => ['required', 'integer', 'min:1', 'max:120'],
            'starts_on'       => ['required', 'date'],
            'notes'           => ['nullable', 'string', 'max:1000'],
            // AC6: the acknowledgement is required only when money exists.
            'acknowledged'    => ['nullable', 'boolean'],
        ]);

        $progress = $this->billing->initialProgress($agency);
        $hasMoney = $this->billing->summary($agency)['payment_count'] > 0;

        if ($hasMoney && ! ($validated['acknowledged'] ?? false)) {
            return response()->json([
                'ok'      => false,
                'message' => 'This agency has payments recorded. Confirm that changing the agreement will not alter them.',
                'errors'  => ['acknowledged' => ['Confirmation required before superseding the plan.']],
            ], 422);
        }

        $before = $this->billing->summary($agency);

        $result = $this->billing->supersedePlan($agency, $validated);

        $after = $this->billing->summary($agency);

        $this->audit(
            $agency,
            'billing_plan_superseded',
            $request,
            [
                'old_plan_id'       => $result['old']?->id,
                'new_plan_id'       => $result['new']->id,
                'old_initial'       => $result['old']?->initial_payment,
                'old_initial_terms' => $result['old']?->initial_terms,
                'old_monthly'       => $result['old']?->monthly_amount,
                'old_terms'         => $result['old']?->terms,
                'new_initial'       => $result['new']->initial_payment,
                'new_initial_terms' => $result['new']->initial_terms,
                'new_monthly'       => $result['new']->monthly_amount,
                'new_terms'         => $result['new']->terms,
                'payments_preserved' => $before['payment_count'],
                'initial_paid_at_change' => $progress['paid'],
            ],
            $after
        );

        return response()->json([
            'ok'      => true,
            'plan_id' => $result['new']->id,
            'summary' => $after,
        ]);
    }

    private function audit(Agency $agency, string $action, Request $request, array $detail, array $afterTotals): void
    {
        $actor = $request->user('godmode');
        if (! $actor) {
            return;
        }

        $this->agencies->writeAudit(
            $agency,
            $action,
            null,
            $detail + [
                'total_paid'          => $afterTotals['total_paid'],
                'initial_outstanding' => $afterTotals['initial_outstanding'],
                'monthly_outstanding' => $afterTotals['monthly_outstanding'],
            ],
            $actor
        );
    }
}
