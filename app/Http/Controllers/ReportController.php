<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ApplicantCertificate;
use App\Models\ApplicantEducation;
use App\Models\ApplicantReference;
use App\Models\ApplicantWorkExperience;
use App\Models\Bill;
use App\Models\Commission;
use App\Models\Country;
use App\Models\OfficialReceipt;
use App\Models\Payment;
use App\Models\StatusCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $this->authorizeAgencyAccess($bill);

        return $this->downloadPdf('reports.bill', ['bill' => $bill->load('employer', 'payments')], 'bill-' . $bill->id . '.pdf');
    }

    public function or(OfficialReceipt $or)
    {
        $this->authorizeAgencyAccess($or);

        return $this->downloadPdf('reports.or', ['or' => $or->load('payment', 'payment.bill')], 'or-' . $or->id . '.pdf');
    }

    public function commission(Commission $commission)
    {
        $this->authorizeAgencyAccess($commission);

        return $this->downloadPdf('reports.commission', ['commission' => $commission], 'commission-' . $commission->id . '.pdf');
    }

    public function resume(Applicant $applicant)
    {
        $this->authorizeAgencyAccess($applicant);

        $applicant->load(['country']);
        $this->loadResumeRelations($applicant);

        $pdf = Pdf::loadView('reports.resume', ['applicant' => $applicant])
            ->setPaper('a4');

        return response($pdf->output(['compress' => 0]), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="resume-' . $applicant->id . '.pdf"');
    }

    public function statistics(): View
    {
        $agencyId = auth()->user()->agency_id;

        $totalApplicants = Applicant::where('agency_id', $agencyId)->count();

        $applicantsByStatus = StatusCode::select(['status_codes.code', 'status_codes.label', 'status_codes.color'])
            ->selectRaw('COUNT(applicants.id) as total')
            ->join('applicants', function ($join) use ($agencyId) {
                $join->on('status_codes.code', '=', 'applicants.status_code')
                    ->where('applicants.agency_id', '=', $agencyId);
            })
            ->groupBy('status_codes.code', 'status_codes.label', 'status_codes.color')
            ->orderBy('status_codes.sort_order')
            ->get();

        $topDestinations = Country::select(['countries.id', 'countries.name'])
            ->selectRaw('COUNT(applicants.id) as total')
            ->join('applicants', function ($join) use ($agencyId) {
                $join->on('countries.id', '=', 'applicants.country_id')
                    ->where('applicants.agency_id', '=', $agencyId);
            })
            ->groupBy('countries.id', 'countries.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $driver = DB::connection()->getDriverName();
        $dateExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', updated_at) as month"
            : "DATE_FORMAT(updated_at, '%Y-%m') as month";

        $monthlyDeployments = Applicant::where('agency_id', $agencyId)
            ->whereNotNull('status_code')
            ->whereIn('status_code', [8, 34])
            ->selectRaw($dateExpr)
            ->selectRaw('COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)
            ->get();

        return view('reports.statistics', compact(
            'totalApplicants',
            'applicantsByStatus',
            'topDestinations',
            'monthlyDeployments',
        ));
    }

    /**
     * Authorize that the authenticated user's agency owns the given model.
     */
    private function authorizeAgencyAccess($model): void
    {
        if ($model->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }
    }

    /**
     * Generate and download a PDF response.
     */
    private function downloadPdf(string $view, array $data, string $filename)
    {
        return Pdf::loadView($view, $data)
            ->setPaper('a4')
            ->download($filename);
    }

    /**
     * Load resume sub-relations outside of the TenantScope.
     */
    private function loadResumeRelations(Applicant $applicant): void
    {
        $applicant->setRelation('education', ApplicantEducation::withoutGlobalScopes()
            ->where('applicant_id', $applicant->id)
            ->orderBy('year_start')->orderBy('year_end')
            ->get());

        $applicant->setRelation('workExperiences', ApplicantWorkExperience::withoutGlobalScopes()
            ->where('applicant_id', $applicant->id)
            ->orderBy('date_to', 'desc')->orderBy('to_date', 'desc')
            ->get());

        $applicant->setRelation('certificates', ApplicantCertificate::withoutGlobalScopes()
            ->where('applicant_id', $applicant->id)
            ->get());

        $applicant->setRelation('references', ApplicantReference::withoutGlobalScopes()
            ->where('applicant_id', $applicant->id)
            ->get());
    }
}
