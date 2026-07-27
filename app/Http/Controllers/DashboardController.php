<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Employer;
use App\Models\StatusCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $agency = tenant_agency();
        $user = auth()->user();

        // Get status counts
        $statusCounts = Applicant::query()
            ->selectRaw('status_code, count(*) as total')
            ->whereNotNull('status_code')
            ->groupBy('status_code')
            ->pluck('total', 'status_code');

        // Get employer counts for pipeline
        $employerCounts = Employer::select(['id', 'name'])
            ->withCount(['applicants' => fn($q) => $q->whereNotNull('status_code'),
        ])->orderByDesc('applicants_count')->limit(10)->get();

        // Get recent applicants, optionally filtered by status and/or employer
        $recentApplicants = Applicant::with('statusCode');
        if (request()->filled('status')) {
            $recentApplicants->where('status_code', request()->integer('status'));
        }
        if (request()->filled('employer')) {
            $recentApplicants->where('employer_id', request()->integer('employer'));
        }
        $recentApplicants = $recentApplicants->latest()->take(10)->get();

        $statusCodes = StatusCode::orderBy('sort_order')->get();

        // Monthly applicant totals (last 12 months) — MySQL/SQLite compatible
        $dbDriver = DB::connection()->getDriverName();
        $dateFormat = $dbDriver === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthlyTotals = Applicant::query()
            ->selectRaw("{$dateFormat} as month, count(*) as total")
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Employer growth
        $employerGrowth = \App\Models\Employer::query()
            ->selectRaw("{$dateFormat} as month, count(*) as total")
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Chart-safe status data
        $chartStatusData = $statusCodes->filter(fn($sc) => ($statusCounts->get($sc->code, 0)) > 0)
            ->values()
            ->map(fn($sc) => [
                'label' => $sc->label,
                'count' => (int)($statusCounts->get($sc->code, 0)),
                'color' => $sc->color ?? '#3b82f6',
            ]);

        return view('dashboard', compact(
            'agency', 'user', 'statusCodes', 'statusCounts', 'recentApplicants',
            'monthlyTotals', 'employerGrowth', 'chartStatusData', 'employerCounts'
        ));
    }
}
