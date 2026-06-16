<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Applicant;
use App\Models\MarketingAgency;
use App\Models\MarketingAgent;
use App\Models\User;
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

    public function worker(Applicant $applicant): View
    {
        if ($applicant->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $bills = Bill::with('payments')
            ->where('applicant_id', $applicant->id)
            ->latest()
            ->get();

        $totalCost = $bills->sum('applicant_cost');
        $totalPaid = $bills->flatMap->payments->sum('amount');
        $balance = $totalCost - $totalPaid;

        return view('accounting.worker', compact(
            'applicant', 'bills',
            'totalCost', 'totalPaid', 'balance'
        ));
    }

    public function marketingAgency(MarketingAgency $marketingAgency): View
    {
        if ($marketingAgency->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $commissions = Commission::where('commissionable_type', 'marketing_agency')
            ->where('commissionable_id', $marketingAgency->id)
            ->latest()
            ->get();

        $totalCommissions = $commissions->sum('amount');
        $totalPaid = $commissions->sum('paid_amount');
        $balance = $totalCommissions - $totalPaid;

        return view('accounting.marketing-agency', compact(
            'marketingAgency', 'commissions',
            'totalCommissions', 'totalPaid', 'balance'
        ));
    }

    public function marketingAgent(MarketingAgent $marketingAgent): View
    {
        if ($marketingAgent->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $commissions = Commission::where('commissionable_type', 'marketing_agent')
            ->where('commissionable_id', $marketingAgent->id)
            ->latest()
            ->get();

        $totalCommissions = $commissions->sum('amount');
        $totalPaid = $commissions->sum('paid_amount');
        $balance = $totalCommissions - $totalPaid;

        return view('accounting.marketing-agent', compact(
            'marketingAgent', 'commissions',
            'totalCommissions', 'totalPaid', 'balance'
        ));
    }

    public function recruitmentAgent(User $recruitmentAgent): View
    {
        if ($recruitmentAgent->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $commissions = Commission::where('commissionable_type', 'recruitment_agent')
            ->where('commissionable_id', $recruitmentAgent->id)
            ->latest()
            ->get();

        $totalCommissions = $commissions->sum('amount');
        $totalPaid = $commissions->sum('paid_amount');
        $balance = $totalCommissions - $totalPaid;

        return view('accounting.recruitment-agent', compact(
            'recruitmentAgent', 'commissions',
            'totalCommissions', 'totalPaid', 'balance'
        ));
    }
}
