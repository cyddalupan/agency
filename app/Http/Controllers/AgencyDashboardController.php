<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Employer;
use App\Models\JobPosition;

class AgencyDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $agency = $user->agency;

        $stats = [
            'total_applicants'    => Applicant::count(),
            'total_employers'     => Employer::count(),
            'total_job_positions' => JobPosition::count(),
            'recent_applicants'   => Applicant::latest()->take(5)->get(),
        ];

        return view('agency.dashboard', compact('user', 'agency', 'stats'));
    }
}
