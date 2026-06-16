<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Bill;
use App\Models\Commission;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function employer(Employer $employer): View
    {
        if ($employer->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $bills = Bill::with('payments')
            ->where('employer_id', $employer->id)
            ->latest()
            ->get();

        $commissions = Commission::where('employer_id', $employer->id)->get();

        $totalBilled = $bills->sum('employer_cost');
        $totalPaid = $bills->flatMap->payments->sum('amount');
        $totalCommissions = $commissions->sum('amount');
        $balance = $totalBilled - $totalPaid;

        return view('accounting.employer', compact(
            'employer', 'bills', 'commissions',
            'totalBilled', 'totalPaid', 'totalCommissions', 'balance'
        ));
    }
}
