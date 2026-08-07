<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceivablesController extends Controller
{
    /**
     * Outstanding bill balances per employer (money owed to the agency).
     * outstanding = (employer_cost + employer_deposit) - confirmed payments.
     * Bills are classified current (not yet due / on-time) vs overdue (past due_date with balance).
     */
    public function receivables(): View
    {
        $agencyId = auth()->user()->agency_id;

        $bills = Bill::with('employer', 'applicant', 'payments')
            ->where('agency_id', $agencyId)
            ->whereHas('employer')
            ->orderBy('due_date')
            ->get();

        $receivables = $bills->map(function (Bill $bill) {
            $billed = (float) $bill->employer_cost + (float) $bill->employer_deposit;
            $paid   = (float) $bill->payments
                ->where('status', 'confirmed')
                ->sum('amount');
            $outstanding = round($billed - $paid, 2);

            $dueDate = $bill->due_date;
            $overdue = $outstanding > 0
                && $dueDate
                && now()->startOfDay()->gt(\Illuminate\Support\Carbon::parse($dueDate)->endOfDay());

            return [
                'bill_id'       => $bill->id,
                'employer_id'   => $bill->employer_id,
                'employer_name' => $bill->employer?->name ?? '—',
                'applicant_id'  => $bill->applicant_id,
                'applicant_name'=> $bill->applicant?->first_name.' '.($bill->applicant?->last_name ?? ''),
                'billed'        => $billed,
                'paid'          => $paid,
                'outstanding'   => $outstanding,
                'due_date'      => $dueDate,
                'status'        => $overdue ? 'overdue' : ($outstanding > 0 ? 'current' : 'paid'),
            ];
        })
            ->filter(fn ($r) => $r['outstanding'] > 0)
            ->sortByDesc('outstanding')
            ->values();

        $overdueCount = $receivables->where('status', 'overdue')->count();
        $totalOutstanding = round($receivables->sum('outstanding'), 2);

        return view('accounting.receivables', compact(
            'receivables', 'overdueCount', 'totalOutstanding'
        ));
    }
}
