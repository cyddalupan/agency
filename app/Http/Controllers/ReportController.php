<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Applicant;
use App\Models\ApplicantCertificate;
use App\Models\ApplicantEducation;
use App\Models\ApplicantReference;
use App\Models\ApplicantWorkExperience;
use App\Models\Bill;
use App\Models\Commission;
use App\Models\Country;
use App\Models\Employer;
use App\Models\ExpenseRequestItem;
use App\Models\OfficialReceipt;
use App\Models\Payment;
use App\Models\StatusCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function applicants(Request $request): View
    {
        $isSuperAdmin = auth()->user()->user_type === 'super_admin';
        $agencyId = auth()->user()->agency_id;

        $query = Applicant::with(['statusCode', 'country', 'employer'])->forBranchUser();

        if (!$isSuperAdmin) {
            $query->where('agency_id', $agencyId);
        }

        if ($request->filled('status_code')) {
            $query->where('status_code', $request->status_code);
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('employer_id')) {
            $query->where('employer_id', $request->integer('employer_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $applicants = $query->latest()->get();
        $statusCodes = StatusCode::orderBy('sort_order')->get();
        $countries = Country::whereIn('id', function ($q) use ($agencyId, $isSuperAdmin) {
            $query = $q->select('country_id')->from('applicants')->whereNotNull('country_id');
            if (!$isSuperAdmin) {
                $query->where('agency_id', $agencyId);
            }
        })->orderBy('name')->get();
        $employers = Employer::orderBy('name')->get(['id', 'name']);

        return view('reports.applicants', compact('applicants', 'statusCodes', 'countries', 'employers'));
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

        $applicant->load(['country', 'agent', 'statusCode', 'skills']);
        $this->loadResumeRelations($applicant);

        $pdf = Pdf::loadView('reports.resume', ['applicant' => $applicant])
            ->setPaper('a4');

        return response($pdf->output(['compress' => 0]), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="resume-' . $applicant->id . '.pdf"');
    }

    /**
     * Expense Report: a printable PDF like Generate CV, showing the applicant's
     * Statement of Account and Agent Expenses (Toybits format).
     */
    public function expenseReport(Applicant $applicant)
    {
        $this->authorizeAgencyAccess($applicant);

        $statementItems = ExpenseRequestItem::with(['expenseRequest', 'account', 'agent'])
            ->where('applicant_id', $applicant->id)
            ->orderBy('id')
            ->get();

        $agentItems = ExpenseRequestItem::with(['expenseRequest', 'account', 'agent'])
            ->where('agent_id', $applicant->agent_id)
            ->where('applicant_id', $applicant->id)
            ->orderBy('id')
            ->get();

        $statementTotals = $statementItems->groupBy('currency')->map(fn ($group) => (float) $group->sum('amount'));

        // Statement total is converted by currency (USD→PHP via free API,
        // 1.0 fallback) so the grand total is meaningful in one currency.
        $converter = app(\App\Services\CurrencyConverter::class);
        $statementGrandTotal = (float) $statementItems->sum(
            fn ($item) => $converter->toPhp((float) $item->amount, (string) $item->currency)
        );
        $agentGrandTotal = (float) $agentItems->sum(
            fn ($item) => $converter->toPhp((float) $item->amount, (string) $item->currency)
        );

        $applicant->load(['agent', 'branch']);

        $pdf = Pdf::loadView('reports.applicant_expense_report', [
            'applicant'           => $applicant,
            'statementItems'      => $statementItems,
            'statementTotals'     => $statementTotals,
            'statementGrandTotal' => $statementGrandTotal,
            'agentItems'          => $agentItems,
            'agentGrandTotal'     => $agentGrandTotal,
        ])->setPaper('a4');

        return response($pdf->output(['compress' => 0]), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="expense-report-' . $applicant->id . '.pdf"');
    }

    public function applicantsExport(Request $request): StreamedResponse
    {
        $isSuperAdmin = auth()->user()->user_type === 'super_admin';
        $agencyId = auth()->user()->agency_id;

        $query = Applicant::with(['statusCode', 'country'])->forBranchUser();

        if (!$isSuperAdmin) {
            $query->where('agency_id', $agencyId);
        }

        if ($request->filled('status_code')) {
            $query->where('status_code', $request->status_code);
        }
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }
        if ($request->filled('employer_id')) {
            $query->where('employer_id', $request->integer('employer_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $applicants = $query->latest()->get();

        $headers = [
            'First Name', 'Last Name', 'Email', 'Contact',
            'Gender', 'Country', 'Status', 'Employer', 'Created At',
        ];

        $callback = function () use ($applicants, $headers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers);

            foreach ($applicants as $applicant) {
                fputcsv($file, [
                    $applicant->first_name,
                    $applicant->last_name,
                    $applicant->email,
                    $applicant->contact,
                    $applicant->gender,
                    $applicant->country?->name ?? 'N/A',
                    $applicant->statusCode?->name ?? 'N/A',
                    $applicant->employer?->name ?? 'N/A',
                    $applicant->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=applicants-report.csv',
        ]);
    }

    public function agents(Request $request): \Illuminate\Contracts\View\View
    {
        $isSuperAdmin = auth()->user()->user_type === 'super_admin';
        $agencyId     = auth()->user()->agency_id;

        $query = Agent::with('agency')->orderBy('name');

        // Agency users must ONLY see their own agency's agents.
        if (! $isSuperAdmin) {
            $query->where('agency_id', $agencyId);
        }

        // Filtering
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $agents = $query->paginate(20);

        return view('reports.agents', compact('agents'));
    }

    public function agentsExport(Request $request): StreamedResponse
    {
        $isSuperAdmin = auth()->user()->user_type === 'super_admin';
        $agencyId     = auth()->user()->agency_id;

        $query = Agent::with('agency')->orderBy('name');

        // Agency users must ONLY see their own agency's agents.
        if (! $isSuperAdmin) {
            $query->where('agency_id', $agencyId);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $agents = $query->get();

        $headers = [
            'Name', 'Email', 'Contact', 'Commission Rate', 'Agency', 'Status', 'Created At',
        ];

        $callback = function () use ($agents, $headers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers);

            foreach ($agents as $agent) {
                fputcsv($file, [
                    $agent->name,
                    $agent->email,
                    $agent->contact ?? '',
                    $agent->commission_rate ? $agent->commission_rate . '%' : '',
                    $agent->agency?->name ?? '',
                    $agent->status,
                    $agent->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=agents-report.csv',
        ]);
    }

    public function statistics(): View
    {
        $isSuperAdmin = auth()->user()->user_type === 'super_admin';
        $agencyId = auth()->user()->agency_id;
        $userBranchId = (int) auth()->user()->branch_id > 0 ? (int) auth()->user()->branch_id : null;

        $totalApplicants = $isSuperAdmin
            ? Applicant::count()
            : Applicant::where('agency_id', $agencyId)->forBranchUser()->count();

        $applicantsByStatus = StatusCode::select(['status_codes.code', 'status_codes.label', 'status_codes.color'])
            ->selectRaw('COUNT(applicants.id) as total')
            ->join('applicants', function ($join) use ($agencyId, $isSuperAdmin, $userBranchId) {
                $join->on('status_codes.code', '=', 'applicants.status_code');
                if (!$isSuperAdmin) {
                    $join->where('applicants.agency_id', '=', $agencyId);
                    if ($userBranchId) {
                        $join->where('applicants.branch_id', '=', $userBranchId);
                    }
                }
            })
            ->groupBy('status_codes.code', 'status_codes.label', 'status_codes.color')
            ->orderBy('status_codes.sort_order')
            ->get();

        $topDestinations = Country::select(['countries.id', 'countries.name'])
            ->selectRaw('COUNT(applicants.id) as total')
            ->join('applicants', function ($join) use ($agencyId, $isSuperAdmin, $userBranchId) {
                $join->on('countries.id', '=', 'applicants.country_id');
                if (!$isSuperAdmin) {
                    $join->where('applicants.agency_id', '=', $agencyId);
                    if ($userBranchId) {
                        $join->where('applicants.branch_id', '=', $userBranchId);
                    }
                }
            })
            ->groupBy('countries.id', 'countries.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $driver = DB::connection()->getDriverName();
        $dateExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', updated_at) as month"
            : "DATE_FORMAT(updated_at, '%Y-%m') as month";

        $monthlyDeployments = $isSuperAdmin
            ? Applicant::whereNotNull('status_code')
                ->whereIn('status_code', [8, 34])
                ->selectRaw($dateExpr)
                ->selectRaw('COUNT(*) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->limit(12)
                ->get()
            : Applicant::where('agency_id', $agencyId)
            ->forBranchUser()
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
