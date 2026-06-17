<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Bill;
use App\Models\Employer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployerBillingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employer = $user->employer;

        $bills = Bill::where('employer_id', $employer->id)
            ->with('payments')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalBilled = $bills->sum('employer_cost');
        $totalPaid = Payment::whereHas('bill', function ($q) use ($employer) {
            $q->where('employer_id', $employer->id);
        })->where('status', 'received')->sum('amount');
        $balance = $totalBilled - $totalPaid;

        return view('employer.billing.index', compact(
            'user', 'employer', 'bills', 'totalBilled', 'totalPaid', 'balance'
        ));
    }

    public function soa()
    {
        $user = Auth::user();
        $employer = $user->employer;

        $bills = Bill::where('employer_id', $employer->id)
            ->with('payments')
            ->orderBy('created_at', 'asc')
            ->get();

        $outstandingBalance = $bills->sum(function ($bill) {
            $paid = $bill->payments->where('status', 'received')->sum('amount');
            return $bill->employer_cost - $paid;
        });

        return view('employer.billing.soa', compact(
            'user', 'employer', 'bills', 'outstandingBalance'
        ));
    }

    public function applicant(Applicant $applicant)
    {
        $user = Auth::user();
        $employer = $user->employer;

        // If the applicant is assigned to a different employer, block access.
        if ($applicant->employer_id !== null && $applicant->employer_id !== $employer->id) {
            abort(404);
        }

        // If the applicant is not assigned to any employer but other employers exist
        // in the agency, block access (ambiguous ownership).
        if ($applicant->employer_id === null) {
            $otherEmployersExist = Employer::where('agency_id', $employer->agency_id)
                ->where('id', '!=', $employer->id)
                ->exists();
            if ($otherEmployersExist) {
                abort(404);
            }
        }

        $bills = Bill::where('employer_id', $employer->id)
            ->where('applicant_id', $applicant->id)
            ->with('payments')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalCost = $bills->sum('applicant_cost');
        $totalPaid = Payment::whereHas('bill', function ($q) use ($employer, $applicant) {
            $q->where('employer_id', $employer->id)
              ->where('applicant_id', $applicant->id);
        })->where('status', 'received')->sum('amount');
        $balance = $totalCost - $totalPaid;

        return view('employer.billing.applicant', compact(
            'user', 'employer', 'applicant', 'bills', 'totalCost', 'totalPaid', 'balance'
        ));
    }
}
