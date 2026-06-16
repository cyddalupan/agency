<?php

namespace App\Http\Controllers;

use App\Models\JobPosition;
use App\Models\Applicant;
use Illuminate\Support\Facades\Auth;

class EmployerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employer = $user->employer;

        $jobPositions = JobPosition::where('employer_id', $employer->id)->get();
        $totalJobs = $jobPositions->count();
        $totalApplicants = Applicant::whereIn('current_job_id', $jobPositions->pluck('id'))->count();

        return view('employer.dashboard', compact(
            'user', 'employer', 'jobPositions', 'totalJobs', 'totalApplicants'
        ));
    }
}
