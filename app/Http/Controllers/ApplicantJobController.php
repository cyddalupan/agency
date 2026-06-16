<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\JobPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicantJobController extends Controller
{
    public function index()
    {
        $applicant = Auth::guard('applicant')->user();
        $jobs = JobPosition::with('employer')
            ->where('agency_id', $applicant->agency_id)
            ->where('status', 'open')
            ->latest()
            ->get();

        return view('portal.jobs.index', compact('jobs'));
    }

    public function show(JobPosition $job)
    {
        $applicant = Auth::guard('applicant')->user();

        // Only allow viewing open jobs from the applicant's own agency
        if ($job->agency_id !== $applicant->agency_id || $job->status !== 'open') {
            abort(404);
        }

        $job->load('employer');

        return view('portal.jobs.show', compact('job'));
    }
}
