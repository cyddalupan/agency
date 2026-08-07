<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\ExpenseRequestItem;
use App\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AgentReportController extends Controller
{
    /**
     * Per-agent ledger: receivables + expenses/collections net, date-range scoped.
     *
     * Columns: Agent, Branch, # Receivables, # Expenses, Total Receivable,
     *          Total Paid/Collected, Expenses (PHP), Balance.
     */
    public function index(Request $request)
    {
        $agencyId = auth()->user()->agency_id;

        $from = $request->input('from');
        $to   = $request->input('to');

        $dateQuery = static function ($query) use ($from, $to) {
            if ($from) {
                $query->whereDate('date', '>=', Carbon::parse($from)->toDateString());
            }
            if ($to) {
                $query->whereDate('date', '<=', Carbon::parse($to)->toDateString());
            }
        };

        // Receivables grouped by agent (agency-scoped + date filter)
        $receivableQuery = Receivable::query()
            ->where('agency_id', $agencyId)
            ->whereNotNull('agent_id');

        $dateQuery($receivableQuery);

        $receivableAgg = (clone $receivableQuery)
            ->select('agent_id')
            ->selectRaw('COUNT(*) as receive_count')
            ->selectRaw('COALESCE(SUM(amount),0) as receive_total')
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN amount ELSE 0 END),0) as collected_total', [Receivable::STATUS_RECEIVED])
            ->groupBy('agent_id')
            ->get()
            ->keyBy('agent_id');

        // Expense line items (agent charge) grouped by agent
        $expenseQuery = ExpenseRequestItem::query()
            ->where('charge', 'agent')
            ->whereNotNull('agent_id')
            ->whereHas('expenseRequest', function ($er) use ($agencyId, $dateQuery) {
                $er->where('agency_id', $agencyId);
                $dateQuery($er);
            });

        $expenseAgg = (clone $expenseQuery)
            ->select('agent_id')
            ->selectRaw('COUNT(*) as expense_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN currency = ? THEN amount ELSE 0 END),0) as expense_php', ['PHP'])
            ->selectRaw('COALESCE(SUM(CASE WHEN currency = ? THEN amount ELSE 0 END),0) as expense_usd', ['USD'])
            ->groupBy('agent_id')
            ->get()
            ->keyBy('agent_id');

        $usdToPhp = (float) config('expense.usd_to_php', 56);

        $agents = Agent::query()
            ->where('agency_id', $agencyId)
            ->with('branch')
            ->get()
            ->map(function (Agent $agent) use ($receivableAgg, $expenseAgg, $usdToPhp) {
                $rec = $receivableAgg->get($agent->id);
                $exp = $expenseAgg->get($agent->id);

                $receiveCount = $rec->receive_count ?? 0;
                $receiveTotal = (float) ($rec->receive_total ?? 0);
                $collected    = (float) ($rec->collected_total ?? 0);

                $expenseCount = $exp->expense_count ?? 0;
                $expensePeso  = (float) ($exp->expense_php ?? 0) + ((float) ($exp->expense_usd ?? 0) * $usdToPhp);

                $balance = $receiveTotal - $collected - $expensePeso;

                return [
                    'id'             => $agent->id,
                    'agent'          => $agent->name,
                    'branch'         => $agent->branch?->name ?? '-',
                    'receive_count'  => (int) $receiveCount,
                    'receive_total'  => $receiveTotal,
                    'collected'      => $collected,
                    'expense_count'  => (int) $expenseCount,
                    'expense_total'  => $expensePeso,
                    'balance'        => $balance,
                ];
            })
            ->sortBy('agent')
            ->values();

        // Global totals across all agents
        $totals = [
            'receive_count' => $agents->sum('receive_count'),
            'receive_total' => $agents->sum('receive_total'),
            'collected'     => $agents->sum('collected'),
            'expense_count' => $agents->sum('expense_count'),
            'expense_total' => $agents->sum('expense_total'),
            'balance'       => $agents->sum('balance'),
        ];

        return view('agent_report.index', compact('agents', 'totals', 'from', 'to', 'usdToPhp'));
    }
}
