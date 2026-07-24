<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\StatusCode;

class AgencyDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $agency = $user->agency;

        // Get status counts
        $statusCounts = Applicant::query()
            ->selectRaw('status_code, count(*) as total')
            ->whereNotNull('status_code')
            ->groupBy('status_code')
            ->pluck('total', 'status_code');

        // Get recent applicants, optionally filtered by status
        $recentQuery = Applicant::with('statusCode');
        if (request()->filled('status')) {
            $recentQuery->where('status_code', request()->integer('status'));
        }
        $recentApplicants = $recentQuery->latest()->take(5)->get();

        $stats = [
            'total_applicants'    => Applicant::count(),
            'total_employers'     => Employer::count(),
            'total_job_positions' => JobPosition::count(),
            'recent_applicants'   => $recentApplicants,
        ];

        $statusCodes = StatusCode::orderBy('sort_order')->get();

        return view('agency.dashboard', compact('user', 'agency', 'stats', 'statusCodes', 'statusCounts'));
    }
}
