<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicantPortalController extends Controller
{
    public function dashboard()
    {
        $applicant = Auth::guard('applicant')->user();

        $logs = $applicant->logs()
            ->orderByDesc('created_at')
            ->get();

        return view('portal.dashboard', compact('applicant', 'logs'));
    }

    public function profile()
    {
        $applicant = Auth::guard('applicant')->user()->load('documents');

        return view('portal.profile', compact('applicant'));
    }
}
