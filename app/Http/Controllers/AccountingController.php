<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Applicant;
use App\Models\MarketingAgency;
use App\Models\MarketingAgent;
use App\Models\User;
use App\Models\Bill;
use App\Models\Commission;
use App\Models\CommissionPayment;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function dashboard(Request $request): View
    {
        $agencyId = auth()->user()->agency_id;

        $from = $request->filled('from') ? $request->date('from') : null;
        $to = $request->filled('to') ? $request->date('to') : null;

        // ---- Money in: payments received (per category) ----
        $payments = Payment::where('agency_id', $agencyId);
        if ($from) $payments->whereDate('payment_date', '>=', $from);
        if ($to)   $payments->whereDate('payment_date', '<=', $to);
        $moneyIn = round((float) $payments->sum('amount'), 2);
        $moneyInByCategory = (clone $payments)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // ---- Money out: expenses + commissions actually paid ----
        $expenses = Expense::with('account.parent')
            ->where('agency_id', $agencyId);
        if ($from) $expenses->whereDate('date', '>=', $from);
        if ($to)   $expenses->whereDate('date', '<=', $to);
        $totalExpenses = round((float) $expenses->sum('amount'), 2);
        $expensesList = (clone $expenses)->get();

        $commissionPayments = CommissionPayment::where('agency_id', $agencyId);
        if ($from) $commissionPayments->whereDate('payment_date', '>=', $from);
        if ($to)   $commissionPayments->whereDate('payment_date', '<=', $to);
        $totalCommissionsPaid = round((float) $commissionPayments->sum('amount'), 2);

        $moneyOut = round($totalExpenses + $totalCommissionsPaid, 2);
        $balance = round($moneyIn - $moneyOut, 2);

        // ---- P&L grouped by account (Main -> Sub) ----
        $expensesByAccount = $expensesList
            ->groupBy('account_id')
            ->map(function ($rows, $accountId) {
                $acc = $rows->first()->account;
                return [
                    'account_name' => $acc->name ?? 'Uncategorized',
                    'parent_name'  => $acc->parent->name ?? null,
                    'total'        => round((float) $rows->sum('amount'), 2),
                ];
            })
            ->values();

        // Income derived from payments by bill category is not account-linked yet;
        // expose payments-by-category as the de-facto income P&L until accounts classify income.
        $incomeByAccount = $moneyInByCategory
            ->map(fn ($total, $cat) => [
                'account_name' => ucwords(str_replace('_', ' ', $cat)),
                'parent_name'  => null,
                'total'        => round((float) $total, 2),
            ])
            ->values();

        // ---- Monthly trend (last 6 months) ----
        $trend = $this->monthlyTrend($agencyId, $from, $to);

        // ---- Entity breakdown (per billing employer) ----
        $moneyInByEntity = $this->entityBreakdown($agencyId, $from, $to);

        $accounts = Account::mains()->with('children')
            ->where('agency_id', $agencyId)
            ->orderBy('type')->orderBy('name')->get();

        return view('accounting.dashboard', compact(
            'moneyIn', 'moneyInByCategory', 'moneyOut', 'totalExpenses',
            'totalCommissionsPaid', 'balance', 'expensesByAccount',
            'incomeByAccount', 'accounts', 'moneyInByEntity'
        ))->with('monthlyTrend', $trend);
    }

    /**
     * Group money-in by billing employer (entity breakdown).
     */
    private function entityBreakdown(int $agencyId, ?string $from, ?string $to): \Illuminate\Support\Collection
    {
        $payments = Payment::with('bill.employer')
            ->where('agency_id', $agencyId)
            ->whereHas('bill.employer');
        if ($from) $payments->whereDate('payment_date', '>=', $from);
        if ($to)   $payments->whereDate('payment_date', '<=', $to);

        $rows = $payments->get()->groupBy(fn ($p) => $p->bill->employer->id)
            ->map(function ($group) {
                $employer = $group->first()->bill->employer;
                return [
                    'employer_name' => $employer->name,
                    'in'            => round((float) $group->sum('amount'), 2),
                ];
            })
            ->values()
            ->sortByDesc('in')
            ->values();

        return $rows;
    }

    /**
     * Export the dashboard P&L as CSV or PDF.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
    {
        $format = $request->query('format', 'csv');
        $agencyId = auth()->user()->agency_id;
        $from = $request->filled('from') ? $request->date('from') : null;
        $to = $request->filled('to') ? $request->date('to') : null;

        // Recompute aggregates (shared with the dashboard view).
        $payments = Payment::where('agency_id', $agencyId);
        if ($from) $payments->whereDate('payment_date', '>=', $from);
        if ($to)   $payments->whereDate('payment_date', '<=', $to);
        $moneyIn = round((float) $payments->sum('amount'), 2);
        $moneyInByCategory = (clone $payments)->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')->pluck('total', 'category');

        $expenses = Expense::with('account.parent')->where('agency_id', $agencyId);
        if ($from) $expenses->whereDate('date', '>=', $from);
        if ($to)   $expenses->whereDate('date', '<=', $to);
        $expensesList = (clone $expenses)->get();
        $totalExpenses = round((float) $expenses->sum('amount'), 2);

        $commissionPayments = CommissionPayment::where('agency_id', $agencyId);
        if ($from) $commissionPayments->whereDate('payment_date', '>=', $from);
        if ($to)   $commissionPayments->whereDate('payment_date', '<=', $to);
        $totalCommissionsPaid = round((float) $commissionPayments->sum('amount'), 2);
        $moneyOut = round($totalExpenses + $totalCommissionsPaid, 2);
        $balance = round($moneyIn - $moneyOut, 2);

        $incomeByAccount = $moneyInByCategory->map(fn ($total, $cat) => [
            'account_name' => ucwords(str_replace('_', ' ', $cat)),
            'total'        => round((float) $total, 2),
        ])->values();

        $expensesByAccount = $expensesList->groupBy('account_id')->map(function ($rows) {
            $acc = $rows->first()->account;
            return [
                'account_name' => $acc->name ?? 'Uncategorized',
                'total'        => round((float) $rows->sum('amount'), 2),
            ];
        })->values();

        $byEntity = $this->entityBreakdown($agencyId, $from, $to);

        $data = compact(
            'moneyIn', 'moneyInByCategory', 'moneyOut', 'totalExpenses',
            'totalCommissionsPaid', 'balance', 'incomeByAccount',
            'expensesByAccount', 'byEntity', 'from', 'to'
        );

        $filename = 'accounting-' . now()->format('Y-m-d');
        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('accounting.export_pdf', $data);
            return $pdf->download($filename . '.pdf');
        }

        // CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];
        $callback = function () use ($data) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Agency Accounting Report', now()->format('Y-m-d H:i')]);
            fputcsv($out, ['Money In', number_format($data['moneyIn'], 2)]);
            fputcsv($out, ['Money Out', number_format($data['moneyOut'], 2)]);
            fputcsv($out, ['Balance', number_format($data['balance'], 2)]);
            fputcsv($out, []);

            fputcsv($out, ['Category', 'Amount']);
            foreach ($data['incomeByAccount'] as $row) {
                fputcsv($out, [$row['account_name'], number_format($row['total'], 2)]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Expense Account', 'Amount']);
            foreach ($data['expensesByAccount'] as $row) {
                fputcsv($out, [$row['account_name'], number_format($row['total'], 2)]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Entity (Employer)', 'Money In']);
            foreach ($data['byEntity'] as $row) {
                fputcsv($out, [$row['employer_name'], number_format($row['in'], 2)]);
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $filename . '.csv', $headers);
    }

    private function monthlyTrend(int $agencyId, ?string $from, ?string $to): array
    {
        // Build a month-keyed series over the last 6 months (or the filtered range).
        $start = $from ? \Illuminate\Support\Carbon::parse($from)->startOfMonth() : now()->subMonths(5)->startOfMonth();
        $end   = $to ? \Illuminate\Support\Carbon::parse($to)->endOfMonth() : now();

        $months = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $months[$cursor->format('Y-m')] = ['month' => $cursor->format('Y-m'), 'in' => 0.0, 'out' => 0.0, 'net' => 0.0];
            $cursor->addMonth();
        }

        // DB-agnostic month grouping (MySQL: DATE_FORMAT, sqlite: strftime).
        $isSqlite = \Illuminate\Support\Facades\DB::getDriverName() === 'sqlite';
        $dateExpr = function (string $col) use ($isSqlite) {
            return $isSqlite
                ? \str_replace('%s', $col, "strftime('%Y-%m', %s)")
                : 'DATE_FORMAT(' . $col . ", '%Y-%m')";
        };

        $pay = Payment::where('agency_id', $agencyId)
            ->selectRaw($dateExpr('payment_date') . " as ym, SUM(amount) as total")
            ->whereNotNull('payment_date')
            ->whereBetween('payment_date', [$start->startOfDay(), $end->endOfDay()])
            ->groupBy('ym')->get();
        foreach ($pay as $p) {
            if (isset($months[$p->ym])) $months[$p->ym]['in'] = round((float) $p->total, 2);
        }

        $exp = Expense::where('agency_id', $agencyId)
            ->selectRaw($dateExpr('date') . " as ym, SUM(amount) as total")
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('ym')->get();
        foreach ($exp as $e) {
            if (isset($months[$e->ym])) $months[$e->ym]['out'] = round((float) $e->total, 2);
        }

        $cp = CommissionPayment::where('agency_id', $agencyId)
            ->selectRaw($dateExpr('payment_date') . " as ym, SUM(amount) as total")
            ->whereNotNull('payment_date')
            ->whereBetween('payment_date', [$start->startOfDay(), $end->endOfDay()])
            ->groupBy('ym')->get();
        foreach ($cp as $c) {
            if (isset($months[$c->ym])) $months[$c->ym]['out'] = round((float) $months[$c->ym]['out'] + (float) $c->total, 2);
        }

        foreach ($months as $k => $m) {
            $months[$k]['net'] = round($m['in'] - $m['out'], 2);
        }

        return array_values($months);
    }

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
