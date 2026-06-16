<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\OfficialReceipt;
use App\Models\Commission;
use App\Models\Applicant;
use App\Models\Country;
use App\Models\StatusCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function applicants(Request $request)
    {
        $agencyId = auth()->user()->agency_id;

        $query = Applicant::where('agency_id', $agencyId)->with(['statusCode', 'country']);

        if ($request->filled('status_code')) {
            $query->where('status_code', $request->status_code);
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $applicants = $query->orderBy('created_at', 'desc')->get();
        $statusCodes = StatusCode::orderBy('sort_order')->get();
        $countries = Country::whereIn('id', function ($q) use ($agencyId) {
            $q->select('country_id')
                ->from('applicants')
                ->where('agency_id', $agencyId)
                ->whereNotNull('country_id');
        })->orderBy('name')->get();

        return view('reports.applicants', compact('applicants', 'statusCodes', 'countries'));
    }
    public function bill(Bill $bill)
    {
        if ($bill->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $bill->load('employer');

        $pdf = Pdf::loadView('reports.bill', compact('bill'));
        return $pdf->download("bill-{$bill->id}.pdf");
    }

    public function or(OfficialReceipt $or)
    {
        if ($or->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $or->load('payment.bill');

        $pdf = Pdf::loadView('reports.or', compact('or'));
        return $pdf->download("or-{$or->id}.pdf");
    }

    public function commission(Commission $commission)
    {
        if ($commission->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $commission->load('employer');

        $pdf = Pdf::loadView('reports.commission', compact('commission'));
        return $pdf->download("commission-{$commission->id}.pdf");
    }
}
