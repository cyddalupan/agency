<?php

declare(strict_types=1);

namespace App\Http\Controllers\GodMode;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Receivable;
use App\Services\GodMode\GodBillingService;
use App\Services\GodMode\GodReceivableService;
use App\Services\GodMode\PaymentLedgerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Card 6b0342ac4cf7cfdc3e26d6a9 — the cross-tenant money ledger.
 *
 * Two GET routes, no writes. There is no store, update, destroy or approve
 * action here and there must never be one (§0): money an agency collected is
 * the agency's record, and an operator edit would break the
 * `receivables` -> `receivable_histories` chain that explains every change.
 *
 * `['auth:godmode', 'godmode.only']` is the whole gate (§4.1). No policy, no
 * `@can`, no role check: `AgencyPolicy::before()` would pass a `super_admin`
 * and its abilities would also pass an `admin`, so reuse would look correct
 * and admit the wrong person.
 *
 * No `Tenant::bypass()` (§4.2): `TenantScope` already opens for a super_admin
 * and a page view is not an audit event.
 */
class GodPaymentsController extends Controller
{
    public function __construct(
        private readonly GodReceivableService $ledger,
        private readonly PaymentLedgerService $paymentLedger,
        private readonly GodBillingService $billingService,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $this->ledger->filters($request->query());

        return view('godmode.payments.index', [
            'filters'      => $filters,
            'bounds'       => $this->ledger->monthBounds($filters),
            'summary'      => $this->ledger->summary($filters),
            'series'       => $this->ledger->monthlySeries($filters),
            'byAccount'    => $this->ledger->byAccount($filters),
            'perAgency'    => $this->ledger->perAgency($filters),
            'ageing'       => $this->ledger->ageing($filters),
            'agencies'     => $this->ledger->agencies(),
            'coverage'     => $this->ledger->agencyCoverage($filters),
            'accounts'     => Receivable::ACCOUNTS,
            'perOptions'   => GodReceivableService::PER_PAGE_OPTIONS,
            'unspecified'  => GodReceivableService::UNSPECIFIED_ACCOUNT,
        ]);
    }

    /**
     * Agency drill-in. `{agency}` binds unscoped on the god plane; an unknown
     * id is a 404 from the implicit binding, never a 500 (§6.19).
     *
     * A `?page=` past the last page is a page that does not exist: `paginate()`
     * returns an empty set and the view would read `first()` as null, so it is
     * a 404 here instead of a 500 in the header (and `page=0`/junk clamps to 1,
     * not to an empty page).
     */
    /**
     * Agency drill-in.
     *
     * Card 6b07f3667e9a74a10e2fe55f re-points this screen at
     * `agency_billing_payments` — what the agency paid LANDAS — instead of
     * `receivables`, which is applicant/agent money inside the agency's own
     * books. The old page answered "what has this agency collected"; the
     * operator asking it actually meant "what has this agency paid us".
     *
     * READ-ONLY (AC8): there is no store/update/void route for this screen.
     * Recording and voiding live in the village modal, on the record-payment
     * card — money entry belongs there, next to the agreement it pays against.
     *
     * `{agency}` binds unscoped on the god plane; an unknown id is a 404 from
     * the implicit binding, never a 500. A `?page=` past the last page is a
     * page that does not exist, so it is a 404 here rather than an empty table
     * under a populated header.
     */
    public function show(Request $request, Agency $agency): View
    {
        $filters = $this->paymentLedger->filters($request->query());

        $payments = $this->paymentLedger->page($agency, $filters);

        abort_if($payments->currentPage() < 1 || $payments->currentPage() > max(1, $payments->lastPage()), 404);

        $totals   = $this->paymentLedger->totals($agency, $filters);
        $summary  = $this->billingService->summary($agency);
        $voidAudit = $this->paymentLedger->voidAudit($agency, $payments->items());

        return view('godmode.payments.show', [
            'agency'      => $agency,
            'filters'     => $filters,
            'payments'    => $payments,
            'totals'      => $totals,
            'summary'     => $summary,
            'plan'        => $summary['plan'],
            'voidAudit'   => $voidAudit,
            'modes'       => Receivable::MODES,
            'perOptions'  => PaymentLedgerService::PER_PAGE_OPTIONS,
            'typeFilters' => PaymentLedgerService::TYPE_FILTERS,
            'hasAny'      => $this->paymentLedger->hasAnyPayment($agency),
        ]);
    }
}
