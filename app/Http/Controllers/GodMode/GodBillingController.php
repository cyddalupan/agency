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
