<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ExpenseRequestItem;
use App\Models\Scopes\ExpenseRequestItemTenantScope;
use App\Services\CurrencyConverter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * General Ledger (Mjolnir card "LANDAS: New Module 'General Ledger'").
 *
 * Groups every expense-request transaction by its main account (AGENT /
 * APPLICANT / OFFICE) and sub account, totals each sub account and each main
 * account, and exposes a per-sub-account "Review" listing plus CSV exports.
 *
 * Amounts are shown in PHP; non-PHP line items are converted with the shared
 * CurrencyConverter (same as the Agents Report).
 */
class GeneralLedgerController extends Controller
{
    /** Display order of the three main account buckets (matches the mock-up). */
    private const CHARGE_TYPE_ORDER = ['agent', 'applicant', 'office'];

    /** Cancelled line items never reach the ledger. */
    private const EXCLUDED_STATUSES = ['cancelled'];

    public function index(): View
    {
        return view('general_ledger.index', $this->ledgerData());
    }

    public function review(Request $request, Account $account): View
    {
        $this->authorizeAccount($account);

        $items = $this->reviewQuery($account)->paginate(print_per_page(50))->withQueryString();

        return view('general_ledger.review', [
            'account' => $account,
            'main'    => $account->parent,
            'items'   => $items,
            'total'   => $this->reviewTotal($account),
        ]);
    }

    public function export(): StreamedResponse
    {
        $data = $this->ledgerData();

        $filename = 'general-ledger-' . now()->format('Y-m-d');

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['General Ledger', now()->format('Y-m-d H:i')]);
            fputcsv($out, []);
            fputcsv($out, ['Main Account', 'Type', 'Sub Accounts', 'Transactions', 'Amount']);

            foreach ($data['groups'] as $group) {
                foreach ($group['subs'] as $sub) {
                    fputcsv($out, [
                        $group['main']->name,
                        ucfirst((string) $group['main']->type),
                        $sub['account']->name,
                        $sub['count'],
                        number_format($sub['amount'], 2, '.', ''),
                    ]);
                }
                fputcsv($out, [
                    $group['main']->name . ' — Total',
                    '',
                    '',
                    $group['count'],
                    number_format($group['total'], 2, '.', ''),
                ]);
                fputcsv($out, []);
            }

            fputcsv($out, ['TOTAL AMOUNT', '', '', $data['grandCount'], number_format($data['grandTotal'], 2, '.', '')]);

            fclose($out);
        }, $filename . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function reviewExport(Account $account): StreamedResponse
    {
        $this->authorizeAccount($account);

        $rows = $this->reviewQuery($account)->get();
        $filename = 'general-ledger-' . str_replace(' ', '-', strtolower($account->name)) . '-' . now()->format('Y-m-d');

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Ref#', 'Date', 'User', 'Status', 'Offices', 'Applicant', agent_header('Agent'),
                'Currency', 'Amount', 'Account', 'Country', 'Particular / Description',
            ]);

            foreach ($rows as $item) {
                fputcsv($out, [
                    $item->expenseRequest->reference_no ?? '',
                    optional($item->expenseRequest->date)->format('Y-m-d') ?? '',
                    $item->expenseRequest->user->name ?? '',
                    $item->statusLabel(),
                    $this->officeName($item),
                    $this->applicantName($item),
                    $item->agent->name ?? '',
                    $item->currency,
                    number_format((float) $item->amount, 2, '.', ''),
                    $item->account->name ?? '',
                    $item->country->name ?? '',
                    $item->particular ?? '',
                ]);
            }

            fclose($out);
        }, $filename . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{groups: \Illuminate\Support\Collection, grandTotal: float, grandCount: int}
     */
    private function ledgerData(): array
    {
        $agencyId = (int) auth()->user()->agency_id;

        $accounts = Account::where('agency_id', $agencyId)->orderBy('name')->get();
        $totals   = $this->accountTotals($agencyId);

        $mains = $accounts->whereNull('parent_id')
            ->sortBy(fn ($a) => array_search($a->charge_type, self::CHARGE_TYPE_ORDER, true) . '|' . $a->name)
            ->values();

        $grandTotal = 0.0;
        $grandCount = 0;

        $groups = $mains->map(function (Account $main) use ($accounts, $totals, &$grandTotal, &$grandCount) {
            $subs = $accounts->where('parent_id', $main->id)
                ->sortBy('name')
                ->map(function (Account $sub) use ($totals) {
                    $row = $totals[$sub->id] ?? ['amount' => 0.0, 'count' => 0];

                    return ['account' => $sub, 'amount' => $row['amount'], 'count' => $row['count']];
                })
                ->values();

            $total = round($subs->sum('amount'), 2);
            $count = (int) $subs->sum('count');

            $grandTotal += $total;
            $grandCount += $count;

            return ['main' => $main, 'subs' => $subs, 'total' => $total, 'count' => $count];
        })->filter(fn ($group) => $group['subs']->isNotEmpty())->values();

        return [
            'groups'     => $groups,
            'grandTotal' => round($grandTotal, 2),
            'grandCount' => $grandCount,
        ];
    }

    /**
     * Sum of non-cancelled line items per account, converted to PHP.
     *
     * Card 6b008b8e7c2a220f1cd6d2e1: this statement joins the items table, so
     * the model's global scope cannot reach it — a toBase() builder is a query
     * builder, and the scope applies to Eloquent builders. The item scope's own
     * constrain() is therefore applied explicitly, with the same
     * expense_requests.agency_id predicate the statement already carried. This
     * is money: the two predicates are the same tenant test, so the totals are
     * unchanged, and the aliased join cannot quietly outlive the scope.
     */
    private function accountTotals(int $agencyId): array
    {
        $rows = ExpenseRequestItemTenantScope::constrain(
            ExpenseRequestItem::query()
                ->join('expense_requests as er', 'er.id', '=', 'expense_request_items.expense_request_id')
                ->where('er.agency_id', $agencyId)
                ->whereNull('er.deleted_at')
                ->whereNotIn('expense_request_items.status', self::EXCLUDED_STATUSES)
                ->groupBy('expense_request_items.account_id', 'expense_request_items.currency')
                ->selectRaw('expense_request_items.account_id as account_id')
                ->selectRaw('expense_request_items.currency as currency')
                ->selectRaw('COUNT(*) as tx_count')
                ->selectRaw('SUM(expense_request_items.amount) as total')
                ->toBase(),
            'expense_request_items',
            $agencyId,
        )->get();

        $converter = new CurrencyConverter();
        $totals    = [];

        foreach ($rows as $row) {
            $accountId = (int) $row->account_id;
            $totals[$accountId] ??= ['amount' => 0.0, 'count' => 0];

            $totals[$accountId]['amount'] += $converter->toPhp((float) $row->total, (string) $row->currency);
            $totals[$accountId]['count']  += (int) $row->tx_count;
        }

        return array_map(fn ($row) => [
            'amount' => round($row['amount'], 2),
            'count'  => $row['count'],
        ], $totals);
    }

    private function reviewQuery(Account $account)
    {
        return ExpenseRequestItem::query()
            ->with(['expenseRequest.user', 'expenseRequest.branch', 'branch', 'applicant', 'agent', 'country', 'account'])
            ->where('account_id', $account->id)
            ->whereNotIn('status', self::EXCLUDED_STATUSES)
            ->whereHas('expenseRequest', function ($query) use ($account) {
                $query->where('agency_id', $account->agency_id)->whereNull('deleted_at');
            })
            ->orderByDesc('id');
    }

    /** @return array{amount: float, count: int} */
    private function reviewTotal(Account $account): array
    {
        $totals = $this->accountTotals((int) $account->agency_id)[$account->id] ?? ['amount' => 0.0, 'count' => 0];

        return $totals + ['count' => 0];
    }

    private function authorizeAccount(Account $account): void
    {
        abort_unless((int) $account->agency_id === (int) auth()->user()->agency_id, 404);
    }

    private function officeName(ExpenseRequestItem $item): string
    {
        return (string) ($item->branch->name ?? $item->expenseRequest->branch->name ?? '');
    }

    private function applicantName(ExpenseRequestItem $item): string
    {
        $applicant = $item->applicant;

        if (! $applicant) {
            return '';
        }

        return trim(implode(' ', array_filter([
            $applicant->first_name, $applicant->middle_name, $applicant->last_name, $applicant->suffix,
        ])));
    }
}
