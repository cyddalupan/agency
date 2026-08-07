<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\StatusCode;
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

        return view('agency.dashboard', compact(
            'user', 'agency', 'stats', 'statusCodes', 'statusCounts',
            'monthlyTotals', 'employerGrowth', 'chartStatusData', 'employerCounts'
        ));
    }
}
