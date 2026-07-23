<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\JobPosition;
use Illuminate\Support\Facades\Auth;

class EmployerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employer = $user->employer;

        $jobPositions = JobPosition::where('employer_id', $employer->id)->get();
        $totalJobs = $jobPositions->count();
        $totalApplicants = Applicant::whereIn('job_id', $jobPositions->pluck('id'))->count();

        return view('employer.dashboard', compact(
            'user', 'employer', 'jobPositions', 'totalJobs', 'totalApplicants'
        ));
    }

    public function applicants()
    {
        $user = Auth::user();
        $employer = $user->employer;

        $applicants = Applicant::where('employer_id', $employer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('employer.applicants', compact(
            'user', 'employer', 'applicants'
        ));
    }
}
