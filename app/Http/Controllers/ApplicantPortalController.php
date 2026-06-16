<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicantPortalController extends Controller
{
    public function dashboard()
    {
        $applicant = Auth::guard('applicant')->user();

        return view('portal.dashboard', compact('applicant'));
    }

    public function profile()
    {
        $applicant = Auth::guard('applicant')->user();

        return view('portal.profile', compact('applicant'));
    }
}
