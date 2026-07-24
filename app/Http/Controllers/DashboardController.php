<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\StatusCode;
use Illuminate\Http\Request;

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

        // Get recent applicants, optionally filtered by status
        $recentApplicants = Applicant::with('statusCode');
        if (request()->filled('status')) {
            $recentApplicants->where('status_code', request()->integer('status'));
        }
        $recentApplicants = $recentApplicants->latest()->take(10)->get();

        $statusCodes = StatusCode::orderBy('sort_order')->get();

        return view('dashboard', compact('agency', 'user', 'statusCodes', 'statusCounts', 'recentApplicants'));
    }
}
