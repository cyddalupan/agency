<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Applicant;
use App\Models\ApplicantCertificate;
use App\Models\ApplicantEducation;
use App\Models\ApplicantReference;
use App\Models\ApplicantWorkExperience;
use App\Models\ApplicantWelfareRecord;
use App\Models\Bill;
use App\Models\Commission;
use App\Models\Country;
use App\Models\Employer;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\OfficialReceipt;
use App\Models\Payment;
use App\Models\StatusCode;
use App\Services\HtmlPdfRenderer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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

        $query = Applicant::with(['statusCode', 'country', 'employer', 'position', 'agent', 'branch'])->forBranchUser();

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

    /**
     * Printable applicant resume / bio-data (PDF).
     *
     * The document is bilingual: field labels are shown in English and in the
     * language of the destination country (the country of the FRA / foreign
     * employer). Rendering goes through HtmlPdfRenderer so non-Latin scripts
     * (Arabic/CJK) shape correctly. (Mjolnir "LANDAS: Resume" 2026-10-08)
     */
    public function resume(Applicant $applicant, HtmlPdfRenderer $renderer)
    {
        $this->authorizeAgencyAccess($applicant);

        $applicant->load([
            'country', 'agent', 'statusCode', 'skills', 'languages',
            'nationality', 'religion', 'civilStatus', 'position', 'branch',
            'agency', 'employer.country', 'passport', 'emergencyContacts',
        ]);
        $this->loadResumeRelations($applicant);

        // Resolve the resume label locale from the destination country of the
        // FRA (foreign employer), falling back to the applicant's destination.
        $countryCode = $applicant->employer?->country?->code ?? $applicant->country?->code;
        $locale = resume_locale_for_country($countryCode) ?? config('app.locale');

        $agency = $applicant->agency ?: resolve_agency();

        $previous = App::getLocale();
        App::setLocale($locale);

        try {
            $html = view('reports.resume', [
                'applicant' => $applicant,
                'agency'    => $agency,
                'locale'    => $locale,
            ])->render();
        } finally {
            App::setLocale($previous);
        }

        $pdf = $renderer->render($html);

        return response($pdf, 200)
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

        // Row split (Toybits 2026-09-07): an item is either charged to the
        // office/applicant (Statement of Account) or to the agent (Agent Expenses).
        // Agent-charged rows must never appear in the applicant's statement.
        // Only released transactions belong on the statement (card 6b06db79):
        // cancelled rows must not appear, and neither do pending/approved/
        // for-releasing ones under the narrow reading of "only Released".
        $statementItems = ExpenseRequestItem::with(['expenseRequest', 'account', 'agent'])
            ->where('applicant_id', $applicant->id)
            ->where('charge', '!=', 'agent')
            ->where('status', ExpenseRequest::STATUS_RELEASED)
            ->orderBy('id')
            ->get();

        $agentItems = ExpenseRequestItem::with(['expenseRequest', 'account', 'agent'])
            ->where('agent_id', $applicant->agent_id)
            ->where('applicant_id', $applicant->id)
            ->where('charge', 'agent')
            ->where('status', ExpenseRequest::STATUS_RELEASED)
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

    /**
     * The one query behind the Reports > Applicants CSV and PDF
     * (card 6b07df1aeec6c1056c38abad). Both must return the same rows.
     */
    private function applicantsReportQuery(Request $request)
    {
        $isSuperAdmin = auth()->user()->user_type === 'super_admin';
        $agencyId = auth()->user()->agency_id;

        // Eager-load every relation a column reads. The screen at
        // reports/applicants.blade.php loads all six; this export loaded two,
        // so the columns it renders were resolved a row at a time.
        $query = Applicant::with(['statusCode', 'country', 'position', 'agent', 'branch'])->forBranchUser();

        if (! $isSuperAdmin) {
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

        return $query->latest();
    }

    private function applicantsReportHeaders(): array
    {
        // The agent heading goes through agent_term(): 'Agent' on Gulf,
        // 'Marketing Staff' on CSDI/FINAS. Same list, different word.
        return [
            'Name', 'Position', agent_header('Agent'), 'Branch',
            'Status', 'Country', 'Contact', 'Email', 'Created At',
        ];
    }

    private function applicantsReportRow(Applicant $applicant): array
    {
        return [
            trim($applicant->first_name.' '.$applicant->last_name),
            $applicant->position?->name ?? '',
            $applicant->agent?->name ?? '',
            $applicant->branch?->name ?? '',
            // ->label, not ->name: StatusCode has no `name` column, so this
            // read returned null and the fallback printed 'N/A' on every row
            // of every export.
            $applicant->statusCode?->label ?? '',
            $applicant->country?->name ?? '',
            $applicant->contact ?? '',
            $applicant->email ?? '',
            $applicant->created_at?->format('Y-m-d') ?? '',
        ];
    }

    public function applicantsExport(Request $request): StreamedResponse
    {
        $applicants = $this->applicantsReportQuery($request)->get();
        $headers = $this->applicantsReportHeaders();

        $callback = function () use ($applicants, $headers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers);

            foreach ($applicants as $applicant) {
                fputcsv($file, $this->applicantsReportRow($applicant));
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=applicants-report.csv',
        ]);
    }

    /**
     * Same rows and columns as the CSV, as a landscape PDF.
     */
    /**
     * Same cap as the applicant module PDFs: DomPDF holds the whole document
     * in memory, and a 256M mod_php limit cannot render Gulf's full applicant
     * set. CSV stays unlimited because it streams.
     */
    private const APPLICANTS_REPORT_PDF_LIMIT = 500;

    public function applicantsExportPdf(Request $request)
    {
        $total = $this->applicantsReportQuery($request)->count();

        if ($total > self::APPLICANTS_REPORT_PDF_LIMIT) {
            return response()->view('applicants.pdf.too-many', [
                'total' => $total,
                'limit' => self::APPLICANTS_REPORT_PDF_LIMIT,
                'csvUrl' => route('reports.applicants.export', $request->only(['status_code', 'country_id', 'employer_id', 'date_from', 'date_to'])),
            ], 200);
        }

        $applicants = $this->applicantsReportQuery($request)->get();

        $rows = $applicants->map(fn ($applicant) => $this->applicantsReportRow($applicant));

        $pdf = Pdf::loadView('applicants.pdf.export', [
            'headers' => $this->applicantsReportHeaders(),
            'rows' => $rows,
            'agency' => auth()->user()->agency,
            'title' => 'Applicants Report',
            'filters' => $this->describeApplicantsReportFilters($request),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        if (method_exists($pdf, 'set_timeout')) {
            $pdf->set_timeout(60);
        }

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="applicants-report.pdf"');
    }

    /**
     * Filters in play, printed on the PDF so the sheet says what it is.
     */
    private function describeApplicantsReportFilters(Request $request): array
    {
        $parts = [];
        if ($request->filled('status_code')) {
            $label = \App\Models\StatusCode::where('code', $request->status_code)->value('label');
            $parts[] = 'Status: '.($label ?: $request->status_code);
        }
        if ($request->filled('country_id')) {
            $name = \App\Models\Country::whereKey($request->country_id)->value('name');
            if ($name) { $parts[] = 'Country: '.$name; }
        }
        if ($request->filled('employer_id')) {
            $name = \App\Models\Employer::whereKey($request->employer_id)->value('name');
            if ($name) { $parts[] = 'Employer: '.$name; }
        }
        if ($request->filled('date_from')) {
            $parts[] = 'From: '.$request->date_from;
        }
        if ($request->filled('date_to')) {
            $parts[] = 'To: '.$request->date_to;
        }

        return $parts;
    }

    public function agents(Request $request): \Illuminate\Contracts\View\View
    {
        $isSuperAdmin = auth()->user()->user_type === 'super_admin';
        $agencyId     = auth()->user()->agency_id;

        // Branch is shown right after Name (CYD 2026-11-06).
        $query = Agent::with(['agency', 'branch'])->orderBy('name');

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

        // CYD 2026-11-06: Agent Report must show all rows on a single page
        // (same as Applicant Report) — no pagination.
        $agents = $query->get();

        return view('reports.agents', compact('agents'));
    }

    public function agentsExport(Request $request): StreamedResponse
    {
        $isSuperAdmin = auth()->user()->user_type === 'super_admin';
        $agencyId     = auth()->user()->agency_id;

        $query = Agent::with(['agency', 'branch'])->orderBy('name');

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

        // Toybits 2026-11-04 (CYD: "Add Branch on Export CSV from Reports"): Branch
        // sits right after Name so the CSV matches the report screenshot.
        $headers = [
            'Name', 'Branch', 'Email', 'Contact', 'Commission Rate', 'Agency', 'Status', 'Created At',
        ];

        $callback = function () use ($agents, $headers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers);

            foreach ($agents as $agent) {
                fputcsv($file, [
                    $agent->name,
                    $agent->branch?->name ?? '',
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

    /**
     * Welfare Report (card 6af81c30 part 3, 2026-11-14).
     *
     * "Remove the Agent (Marketing) Report on Report Module, Instead Add
     * Welfare Report." Replaces the reports.agents card on FINAS and CSDI.
     *
     * Columns mirror the Welfare module so the two read the same way.
     */
    public function welfare(Request $request): View
    {
        abort_unless(
            \App\Http\Controllers\WelfareController::enabled(auth()->user()->agency_id ?? 0),
            403,
            'The Welfare Report is not available for this agency.'
        );

        $query = $this->welfareQuery($request);

        return view('reports.welfare', [
            'records'   => $query->orderByDesc('date_of_contact')->orderByDesc('id')->get(),
            'countries' => Country::orderBy('name')->get(),
        ]);
    }

    public function welfareExport(Request $request): StreamedResponse
    {
        abort_unless(
            \App\Http\Controllers\WelfareController::enabled(auth()->user()->agency_id ?? 0),
            403,
            'The Welfare Report is not available for this agency.'
        );

        $rows = $this->welfareQuery($request)
            ->orderByDesc('date_of_contact')->orderByDesc('id')->get();

        $headers = [
            'Name', 'Passport No.', 'DMW E-Reg ID', 'Country', 'Job Site City',
            'Position', 'FRA', 'Remarks / Issues / Actions Taken',
            'Encoder', 'Date of Contact', 'Direct Employer', 'Contact Abroad',
        ];

        return response()->streamDownload(function () use ($rows, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $w) {
                fputcsv($out, [
                    $w->ofw_full_name,
                    $w->passport_number,
                    $w->dmw_ereg_id,
                    $w->country_of_destination,
                    $w->job_site_city,
                    $w->position_designation,
                    $w->foreign_recruitment_agency,
                    $w->remarks,
                    $w->encoder ?: $w->logged_by,
                    optional($w->date_of_contact)->format('Y-m-d'),
                    $w->direct_employer_name,
                    $w->contact_no_abroad,
                ]);
            }
            fclose($out);
        }, 'welfare-report-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    /** Shared filters for the Welfare Report and its CSV. */
    private function welfareQuery(Request $request)
    {
        $query = ApplicantWelfareRecord::where('agency_id', auth()->user()->agency_id);

        if ($v = trim((string) $request->query('name'))) {
            $query->where('ofw_full_name', 'like', "%{$v}%");
        }
        if ($v = trim((string) $request->query('country'))) {
            $query->where('country_of_destination', 'like', "%{$v}%");
        }
        if ($v = trim((string) $request->query('fra'))) {
            $query->where('foreign_recruitment_agency', 'like', "%{$v}%");
        }
        if ($request->filled('from')) {
            $query->whereDate('date_of_contact', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('date_of_contact', '<=', $request->query('to'));
        }

        return $query;
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
