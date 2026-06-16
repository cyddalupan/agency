<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\OfficialReceipt;
use App\Models\Commission;
use App\Models\Applicant;
use App\Models\Country;
use App\Models\StatusCode;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function applicants(Request $request): View
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

        $applicants = $query->latest()->get();
        $statusCodes = StatusCode::orderBy('sort_order')->get();
        $countries = Country::whereIn('id', function ($q) use ($agencyId) {
            $q->select('country_id')->from('applicants')->where('agency_id', $agencyId)->whereNotNull('country_id');
        })->orderBy('name')->get();

        return view('reports.applicants', compact('applicants', 'statusCodes', 'countries'));
    }

    public function transactions(): View
    {
        $agencyId = auth()->user()->agency_id;

        $bills = Bill::with(['employer', 'applicant', 'payments'])
            ->where('agency_id', $agencyId)
            ->latest()
            ->get();

        $payments = Payment::with(['bill.employer', 'bill.applicant'])
            ->where('agency_id', $agencyId)
            ->latest()
            ->get();

        $totalBilled = $bills->sum('employer_cost');
        $totalPaid = $payments->sum('amount');

        return view('transactions.index', compact('bills', 'payments', 'totalBilled', 'totalPaid'));
    }

    public function bill(Bill $bill)
    {
        if ($bill->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        return Pdf::loadView('reports.bill', ['bill' => $bill->load('employer', 'payments')])
            ->setPaper('a4')
            ->download('bill-' . $bill->id . '.pdf');
    }

    public function or(OfficialReceipt $or)
    {
        if ($or->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        return Pdf::loadView('reports.or', ['or' => $or->load('payment', 'payment.bill')])
            ->setPaper('a4')
            ->download('or-' . $or->id . '.pdf');
    }

    public function commission(Commission $commission)
    {
        if ($commission->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        return Pdf::loadView('reports.commission', ['commission' => $commission])
            ->setPaper('a4')
            ->download('commission-' . $commission->id . '.pdf');
    }
}
