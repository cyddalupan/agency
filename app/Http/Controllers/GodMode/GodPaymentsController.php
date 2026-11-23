<?php

declare(strict_types=1);

namespace App\Http\Controllers\GodMode;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Receivable;
use App\Services\GodMode\GodReceivableService;
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
    public function __construct(private readonly GodReceivableService $ledger)
    {
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
    public function show(Request $request, Agency $agency): View
    {
        $query = $request->query();
        $query['agency'] = $agency->id;
        $filters = $this->ledger->filters($query);

        $rows = $this->ledger->agencyPage($filters, $agency->id);

        abort_if($rows->currentPage() < 1 || $rows->currentPage() > $rows->lastPage(), 404);

        return view('godmode.payments.show', [
            'filters'   => $filters,
            'bounds'    => $this->ledger->monthBounds($filters),
            'agency'    => $agency,
            'header'    => $this->ledger->agencyHeader($agency),
            'summary'   => $this->ledger->summary($filters),
            'series'    => $this->ledger->monthlySeries($filters),
            'byAccount' => $this->ledger->byAccount($filters),
            'rows'      => $rows,
            'voided'    => $this->ledger->voidedPage($filters, $agency->id),
            'perOptions' => GodReceivableService::PER_PAGE_OPTIONS,
            'accounts'  => Receivable::ACCOUNTS,
            'unspecified' => GodReceivableService::UNSPECIFIED_ACCOUNT,
        ]);
    }
}
