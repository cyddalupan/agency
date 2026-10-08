<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\StatusCode;
use App\Services\ExpiryNotificationService;
use Illuminate\Support\Facades\DB;

class AgencyDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $agency = $user->agency;

        // Get status counts
        $statusCounts = Applicant::query()
            ->forBranchUser()
            ->selectRaw('status_code, count(*) as total')
            ->whereNotNull('status_code')
            ->groupBy('status_code')
            ->pluck('total', 'status_code');

        // Get employer counts for pipeline (branch-scoped for branch accounts)
        $employerCounts = Employer::select(['id', 'name'])
            ->withCount(['applicants' => fn($q) => $q->forBranchUser()->whereNotNull('status_code'),
        ])->orderByDesc('applicants_count')->limit(10)->get();

        // Get recent applicants, optionally filtered by status and/or employer
        $recentQuery = Applicant::with('statusCode')->forBranchUser();
        if (request()->filled('status')) {
            $recentQuery->where('status_code', request()->integer('status'));
        }
        if (request()->filled('employer')) {
            $recentQuery->where('employer_id', request()->integer('employer'));
        }
        $recentApplicants = $recentQuery->latest()->take(5)->get();

        $stats = [
            'total_applicants'    => Applicant::forBranchUser()->count(),
            'total_employers'     => Employer::count(),
            'total_job_positions' => JobPosition::count(),
            'recent_applicants'   => $recentApplicants,
        ];

        $statusCodes = StatusCode::orderBy('sort_order')->get();

        // Chart data
        $dbDriver = DB::connection()->getDriverName();
        $dateFormat = $dbDriver === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthlyTotals = Applicant::query()
            ->forBranchUser()
            ->selectRaw("{$dateFormat} as month, count(*) as total")
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $employerGrowth = Employer::query()
            ->selectRaw("{$dateFormat} as month, count(*) as total")
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $chartStatusData = $statusCodes->filter(fn($sc) => ($statusCounts->get($sc->code, 0)) > 0)
            ->values()
            ->map(fn($sc) => [
                'label' => $sc->label,
                'count' => (int)($statusCounts->get($sc->code, 0)),
                'color' => $sc->color ?? '#3b82f6',
            ]);

        // ── Deployment Pipeline (table form) ────────────────────────────────
        // Mjolnir card "DEPLOYMENT PIPELINE - (Table form)", 2026-10-08.
        // One row per FRA (employer), one column per stage, plus a TOTAL row.
        // Stage labels fold the round-2 variants ("Reserved 2" → Reserved, …).
        $pipelineStages = [
            'Reserved'  => ['Reserved', 'Reserved 2'],
            'Selected'  => ['Selected', 'Selected 2'],
            'Interview' => ['Interview', 'Interview 2'],
            'Contract'  => ['Contract', 'Contract 2'],
            'OEC'       => ['OEC'],
            'OWWA'      => ['OWWA'],
            'Visa'      => ['Visa'],
            'Deployed'  => ['Deployed', 'Deployed 2'],
            'Repat'     => ['Repatriated'],
            'Backout'   => ['Backout'],
        ];

        $labelToCode = StatusCode::pluck('code', 'label');
        $codeToStage = [];
        foreach ($pipelineStages as $stage => $labels) {
            foreach ($labels as $label) {
                if (isset($labelToCode[$label])) {
                    $codeToStage[$labelToCode[$label]] = $stage;
                }
            }
        }
        $stageCodes = array_keys($codeToStage);

        $pipelineYear    = request('pipeline_year');
        $pipelineMonth   = request('pipeline_month');
        $pipelineCountry = request('pipeline_country');

        $stageTotalsByEmployer = [];
        $pipelineRows = Applicant::query()
            ->forBranchUser()
            ->whereIn('status_code', $stageCodes)
            ->when($pipelineYear, fn ($q) => $q->whereYear('created_at', (int) $pipelineYear))
            ->when($pipelineYear && $pipelineMonth, fn ($q) => $q->whereMonth('created_at', (int) $pipelineMonth))
            ->when($pipelineCountry, fn ($q) => $q->whereHas('employer', fn ($e) => $e->where('country_id', (int) $pipelineCountry)))
            ->selectRaw('employer_id, status_code, count(*) as total')
            ->groupBy('employer_id', 'status_code')
            ->get();

        foreach ($pipelineRows as $row) {
            $stage = $codeToStage[$row->status_code] ?? null;
            if (! $stage || ! $row->employer_id) {
                continue;
            }
            $stageTotalsByEmployer[$row->employer_id][$stage] =
                ($stageTotalsByEmployer[$row->employer_id][$stage] ?? 0) + (int) $row->total;
        }

        $pipelineEmployers = Employer::whereIn('id', array_keys($stageTotalsByEmployer))
            ->orderBy('name')
            ->get(['id', 'name']);

        $pipelineTotals = [];
        foreach (array_keys($pipelineStages) as $stage) {
            $pipelineTotals[$stage] = array_sum(array_map(
                fn ($byStage) => $byStage[$stage] ?? 0,
                $stageTotalsByEmployer
            ));
        }

        $pipelineCountries = \App\Models\Country::orderBy('name')->get(['id', 'name']);
        $pipelineYears     = range((int) date('Y'), (int) date('Y') - 4);

        // ── Expiry pop-up ───────────────────────────────────────────────────
        // Mjolnir card "LANDAS: POP-UP Expire notification", 2026-10-08.
        // Medicals expiring within 2 weeks and visas within 1 month.
        $expiryService    = app(ExpiryNotificationService::class);
        $expiringMedicals = $expiryService->medicals($user);
        $expiringVisas    = $expiryService->visas($user);

        return view('agency.dashboard', compact(
            'user', 'agency', 'stats', 'statusCodes', 'statusCounts',
            'monthlyTotals', 'employerGrowth', 'chartStatusData', 'employerCounts',
            'pipelineStages', 'pipelineEmployers', 'stageTotalsByEmployer',
            'pipelineTotals', 'pipelineCountries', 'pipelineYears',
            'expiringMedicals', 'expiringVisas'
        ));
    }
}
