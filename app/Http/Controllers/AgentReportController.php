<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\Applicant;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\Receivable;
use App\Models\StartingBalance;
use App\Services\CurrencyConverter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AgentReportController extends Controller
{
    /** Withdrawn/Repat statuses: 35 Repatriated, 38 Cancel, 50 Backout. */
    private const BACKOUT_STATUSES = [35, 38, 50];

    /** Agent Report tabs (7 per spec: Commission, Cash Advance, Receivables, Payments, Deductions, Starting Balance, Report). */
    private const TABS = ['commission', 'cash-advance', 'receivables', 'payments', 'deductions', 'starting-balances', 'report'];

    /** Account names that route to the Receivables tab (moved out of Commission). */
    private const RECEIVABLE_ACCOUNT_NAMES = ['Partial', 'Full', 'Fit to work', 'Contract', 'Deployed', 'Return'];

    /** Account names that route to the Cash Advance tab (moved out of Commission). */
    private const CASH_ADVANCE_ACCOUNT_NAMES = ['Cash advance', 'Agent Advances'];

    /**
     * 5-tab Agents Report page.
     *
     * Tabs:
     *  1. commission        — ExpenseRequest items charged to agents
     *     ("Agent Commission Pending Transaction")
     *  2. payments          — Receivable module rows
     *  3. deductions        — new AgentDeduction entries (Paid | Deduction)
     *  4. starting-balances — new StartingBalance entries (one per agent)
     *  5. report            — per-agent ledger, downloadable/printable
     *
     * Ledger format (Agent Ledger): Total Commission · Total Cash Advance ·
     * Total Backout and Repat · Total Receivable (AR) · Total Payments ·
     * Agent's Balance (Balance = Cash Advance + Backout & Repat
     * − Receivable (AR) − Payments).
     */
    public function index(Request $request): View
    {
        $agencyId = auth()->user()->agency_id;

        $tab = $request->input('tab', 'commission');
        if (! in_array($tab, self::TABS, true)) {
            $tab = 'commission';
        }
        $agentId = $request->input('agent_id');

        $agents = $this->scopeAgentsByBranch(Agent::where('agency_id', $agencyId))
            ->with('branch')
            ->orderBy('name')
            ->get();

        $converter = new CurrencyConverter();

        // Account IDs for tab-specific routing (by name, scoped to the agency)
        $receivableAccountIds = Account::where('agency_id', $agencyId)
            ->whereIn('name', self::RECEIVABLE_ACCOUNT_NAMES)
            ->pluck('id');

        $cashAdvanceAccountIds = Account::where('agency_id', $agencyId)
            ->whereIn('name', self::CASH_ADVANCE_ACCOUNT_NAMES)
            ->pluck('id');

        $excludedAccountIds = $receivableAccountIds->merge($cashAdvanceAccountIds)->unique();

        // ---- Tab 1: Commission (pending agent-charged items, excluding Receivables & Cash Advance) ----
        // Only PENDING requests show here; approved items become Paid entries
        // in the Deductions & Paid tab (Toybits 2026-08-29).
        $commissionItems = ExpenseRequestItem::query()
            ->where('charge', 'agent')
            ->whereNotNull('agent_id')
            ->whereHas('expenseRequest', fn ($er) => $er->where('agency_id', $agencyId)->where('status', ExpenseRequest::STATUS_PENDING))
            ->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)))
            ->when($excludedAccountIds->isNotEmpty(), fn ($q) => $q->whereNotIn('account_id', $excludedAccountIds))
            ->with(['expenseRequest.user', 'expenseRequest.branch', 'applicant', 'agent', 'account', 'country'])
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->orderByDesc('created_at')
            ->get();

        // ---- Tab 1b: Cash Advance (pending agent-charged items on Cash Advance accounts) ----
        $cashAdvanceItems = ExpenseRequestItem::query()
            ->where('charge', 'agent')
            ->whereNotNull('agent_id')
            ->whereHas('expenseRequest', fn ($er) => $er->where('agency_id', $agencyId)->where('status', ExpenseRequest::STATUS_PENDING))
            ->when($cashAdvanceAccountIds->isNotEmpty(), fn ($q) => $q->whereIn('account_id', $cashAdvanceAccountIds))
            ->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)))
            ->with(['expenseRequest.user', 'expenseRequest.branch', 'applicant', 'agent', 'account', 'country'])
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->orderByDesc('created_at')
            ->get();

        // ---- Tab 1c: Receivables (pending agent-charged items on the six receivable accounts) ----
        $receivableItems = ExpenseRequestItem::query()
            ->where('charge', 'agent')
            ->whereNotNull('agent_id')
            ->whereHas('expenseRequest', fn ($er) => $er->where('agency_id', $agencyId)->where('status', ExpenseRequest::STATUS_PENDING))
            ->when($receivableAccountIds->isNotEmpty(), fn ($q) => $q->whereIn('account_id', $receivableAccountIds))
            ->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)))
            ->with(['expenseRequest.user', 'expenseRequest.branch', 'applicant', 'agent', 'account', 'country'])
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->orderByDesc('created_at')
            ->get();

        // ---- Tab 2: Payments (Receivable module) ----
        $payments = Receivable::query()
            ->where('agency_id', $agencyId)
            ->with(['agent', 'applicant', 'encoder'])
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        // ---- Tab 3: Deductions & Paid (new table) ----
        $deductions = AgentDeduction::query()
            ->where('agency_id', $agencyId)
            ->with(['agent', 'applicant', 'encoder'])
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        // ---- Tab 4: Starting Balance (new table) ----
        $startingBalances = StartingBalance::query()
            ->where('agency_id', $agencyId)
            ->with(['agent', 'applicant', 'encoder'])
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        // ---- Tab 5: Report (per-agent ledger, new formula) ----
        $reportRows = $this->buildReportRows($agents, $converter, $agentId);

        $totals = [
            'commission'    => $reportRows->sum('commission'),
            'cash_advance'  => $reportRows->sum('cash_advance'),
            'backout_repat' => $reportRows->sum('backout_repat'),
            'receivable'    => $reportRows->sum('receivable'),
            'payments'      => $reportRows->sum('payments'),
            'balance'       => $reportRows->sum('balance'),
        ];

        return view('agent_report.index', compact(
            'tab',
            'agents',
            'agentId',
            'commissionItems',
            'cashAdvanceItems',
            'receivableItems',
            'payments',
            'deductions',
            'startingBalances',
            'reportRows',
            'totals',
            'converter'
        ) + ['canManageDeductions' => $this->canManageDeductions()]);
    }

    /**
     * (Cyd 2026-09-26) Only Admin (super_admin/admin) and Accounting (billing)
     * may create Deduction transactions. Everyone else has the tab hidden and
     * the create/store routes return 403.
     */
    private function canManageDeductions(): bool
    {
        $user = auth()->user();

        // (Cyd 2026-09-28 #11) Only the privileged accounts (Mae, Evelyn,
        // Angel) may create agent deductions. Everyone else: tab hidden +
        // create/store 403.
        return $user && $user->isPrivileged();
    }

    // ------------------------------------------------------------------
    // Tab 3: Deductions & Paid
    // ------------------------------------------------------------------

    public function deductionCreate(): View
    {
        abort_unless($this->canManageDeductions(), 403, 'Only Admin and Accounting can create deduction transactions.');

        [$agents, $applicants] = $this->agentApplicantLists();

        return view('agent_report.deduction_create', [
            'agents'     => $agents,
            'applicants' => $applicants,
        ]);
    }

    public function deductionStore(Request $request): RedirectResponse
    {
        abort_unless($this->canManageDeductions(), 403, 'Only Admin and Accounting can create deduction transactions.');

        $agencyId = auth()->user()->agency_id;

        $validated = $request->validate([
            'date'         => ['required', 'date'],
            'agent_id'     => ['required', 'integer', 'exists:agents,id'],
            'applicant_id' => ['nullable', 'integer', 'exists:applicants,id'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'particular'   => ['nullable', 'string'],
        ]);

        $agent = Agent::where('agency_id', $agencyId)->findOrFail($validated['agent_id']);
        $this->authorizeAgentBranch($agent);

        if (! empty($validated['applicant_id'])) {
            $applicant = Applicant::where('agency_id', $agencyId)->find($validated['applicant_id']);
            if (! $applicant || $applicant->agent_id !== $agent->id) {
                return back()->withErrors(['applicant_id' => 'Applicant must belong to the selected agent.'])->withInput();
            }
        }

        $validated['agency_id'] = $agencyId;
        $validated['user_id']   = auth()->id();
        $validated['account']   = AgentDeduction::ACCOUNT_DEDUCTION; // deductions only (Paid no longer available)

        AgentDeduction::create($validated);

        return redirect()->route('agent_report.index', ['tab' => 'deductions'])
            ->with('success', "{$validated['account']} entry saved.");
    }

    /**
     * Review — admin-only status/history behavior mirrors Receivable review,
     * but AgentDeduction has no status field: this is a read-only review page.
     */
    public function deductionShow(AgentDeduction $agentDeduction): View
    {
        $this->authorizeAgency($agentDeduction->agency_id);
        $this->authorizeAgentBranch($agentDeduction->agent);

        return view('agent_report.deduction_show', [
            'deduction' => $agentDeduction->load(['agent', 'applicant', 'encoder']),
        ]);
    }

    // ------------------------------------------------------------------
    // Tab 4: Starting Balance
    // ------------------------------------------------------------------

    public function startingBalanceCreate(): View
    {
        [$agents, $applicants] = $this->agentApplicantLists();

        return view('agent_report.starting_balance_create', [
            'agents'     => $agents,
            'applicants' => $applicants,
            'account'    => StartingBalance::ACCOUNT,
        ]);
    }

    public function startingBalanceStore(Request $request): RedirectResponse
    {
        $agencyId = auth()->user()->agency_id;

        $validated = $request->validate([
            'date'         => ['required', 'date'],
            'agent_id'     => ['required', 'integer', 'exists:agents,id'],
            'applicant_id' => ['nullable', 'integer', 'exists:applicants,id'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'particular'   => ['nullable', 'string'],
        ]);

        $agent = Agent::where('agency_id', $agencyId)->findOrFail($validated['agent_id']);
        $this->authorizeAgentBranch($agent);

        // One starting balance per agent (unique agency_id + agent_id)
        if (StartingBalance::where('agency_id', $agencyId)->where('agent_id', $agent->id)->exists()) {
            return back()->withErrors(['agent_id' => 'This agent already has a starting balance.'])->withInput();
        }

        if (! empty($validated['applicant_id'])) {
            $applicant = Applicant::where('agency_id', $agencyId)->find($validated['applicant_id']);
            if (! $applicant || $applicant->agent_id !== $agent->id) {
                return back()->withErrors(['applicant_id' => 'Applicant must belong to the selected agent.'])->withInput();
            }
        }

        StartingBalance::create([
            'agency_id'    => $agencyId,
            'user_id'      => auth()->id(),
            'agent_id'     => $agent->id,
            'applicant_id' => $validated['applicant_id'] ?? null,
            'date'         => $validated['date'],
            'account'      => StartingBalance::ACCOUNT,
            'amount'       => $validated['amount'],
            'particular'   => $validated['particular'] ?? null,
        ]);

        return redirect()->route('agent_report.index', ['tab' => 'starting-balances'])
            ->with('success', 'Starting balance saved.');
    }

    public function startingBalanceShow(StartingBalance $startingBalance): View
    {
        $this->authorizeAgency($startingBalance->agency_id);
        $this->authorizeAgentBranch($startingBalance->agent);

        return view('agent_report.starting_balance_show', [
            'startingBalance' => $startingBalance->load(['agent', 'applicant', 'encoder']),
        ]);
    }

    // ------------------------------------------------------------------
    // Tab 5: Report — print / CSV export
    // ------------------------------------------------------------------

    public function print(Request $request): View
    {
        $agencyId = auth()->user()->agency_id;
        $agentId  = $request->input('agent_id');

        $agents = $this->scopeAgentsByBranch(Agent::where('agency_id', $agencyId))->with('branch')->orderBy('name')->get();
        $converter = new CurrencyConverter();

        $rows = $this->buildReportRows($agents, $converter, $agentId);

        return view('agent_report.print', [
            'agents'    => $agents,
            'agentId'   => $agentId,
            'reportRows' => $rows,
            'totals'    => [
                'commission'    => $rows->sum('commission'),
                'cash_advance'  => $rows->sum('cash_advance'),
                'backout_repat' => $rows->sum('backout_repat'),
                'receivable'    => $rows->sum('receivable'),
                'payments'      => $rows->sum('payments'),
                'balance'       => $rows->sum('balance'),
            ],
            'printedAt' => now(),
        ]);
    }

    public function export(Request $request)
    {
        $agencyId = auth()->user()->agency_id;
        $agentId  = $request->input('agent_id');

        $agents = $this->scopeAgentsByBranch(Agent::where('agency_id', $agencyId))->with('branch')->orderBy('name')->get();
        $converter = new CurrencyConverter();

        $rows = $this->buildReportRows($agents, $converter, $agentId);

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, [
            'Agent', 'Branch', 'Total Commission', 'Total Cash Advance',
            'Total Backout and Repat', 'Total Receivable (AR)',
            'Total Payments', "Agent's Balance",
        ]);
        foreach ($rows as $row) {
            fputcsv($csv, [
                $row['agent'],
                $row['branch'],
                number_format($row['commission'], 2),
                number_format($row['cash_advance'], 2),
                number_format($row['backout_repat'], 2),
                number_format($row['receivable'], 2),
                number_format($row['payments'], 2),
                number_format($row['balance'], 2),
            ]);
        }
        rewind($csv);
        $contents = stream_get_contents($csv);
        fclose($csv);

        return response($contents, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="agent-ledger-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    // ------------------------------------------------------------------
    // Agent Ledger — single-agent detail view (clickable Name) + PDF
    // ------------------------------------------------------------------

    /** Single-agent ledger view: five detail tables + summary. */
    public function show(Agent $agent): View
    {
        $this->authorizeAgency($agent->agency_id);
        $this->authorizeAgentBranch($agent);

        return view('agent_report.show', $this->agentLedgerData($agent));
    }

    /** Print/PDF version of the single-agent ledger. */
    public function showPrint(Agent $agent): View
    {
        $this->authorizeAgency($agent->agency_id);
        $this->authorizeAgentBranch($agent);

        $data = $this->agentLedgerData($agent);
        $data['printedAt'] = now();

        return view('agent_report.show_print', $data);
    }

    /**
     * Gathers every table + total for the single-agent ledger view.
     *  - releasedCommission : RELEASED expense items on commission accounts
     *    (Partial/Full/Fit to work/Contract/Deployed/Return); Return rows are
     *    shown but excluded from the Total Commission.
     *  - cashAdvance        : RELEASED expense items on cash-advance accounts
     *  - backoutRepat       : AgentDeduction rows (account = Deduction)
     *  - receivables        : Receivable module rows (account = Agents &
     *    Applicant Payment, status = received)
     *  - payments           : AgentDeduction rows (account = Paid)
     *  - balance            = cashAdvance + backoutRepat - receivables - payments
     */
    private function agentLedgerData(Agent $agent): array
    {
        $agencyId = $agent->agency_id;
        $converter = new CurrencyConverter();

        $commissionAccountIds = Account::where('agency_id', $agencyId)
            ->whereIn('name', self::RECEIVABLE_ACCOUNT_NAMES)
            ->pluck('id');
        $returnAccountIds = Account::where('agency_id', $agencyId)
            ->where('name', 'Return')
            ->pluck('id');
        $cashAdvanceAccountIds = Account::where('agency_id', $agencyId)
            ->whereIn('name', self::CASH_ADVANCE_ACCOUNT_NAMES)
            ->pluck('id');

        // Released agent-charged expense items for this agent.
        $items = ExpenseRequestItem::query()
            ->where('charge', 'agent')
            ->where('agent_id', $agent->id)
            ->whereHas('expenseRequest', fn ($er) => $er
                ->where('agency_id', $agencyId)
                ->where('status', ExpenseRequest::STATUS_RELEASED))
            ->with(['expenseRequest', 'applicant', 'account'])
            ->orderByDesc('id')
            ->get();

        $releasedCommission = $items->whereIn('account_id', $commissionAccountIds)->values();
        $cashAdvance        = $items->whereIn('account_id', $cashAdvanceAccountIds)->values();

        // Total Commission = all commission accounts MINUS Return.
        $totalCommission = $releasedCommission
            ->reject(fn ($i) => $returnAccountIds->contains($i->account_id))
            ->sum(fn ($i) => $converter->toPhp((float) $i->amount, $i->currency));
        $totalCashAdvance = $cashAdvance->sum(fn ($i) => $converter->toPhp((float) $i->amount, $i->currency));

        // Backout & Repat (Deduction) and Payment (Paid) from the Deduction & Paid module.
        $deductions = AgentDeduction::query()
            ->where('agent_id', $agent->id)
            ->with(['agent', 'applicant'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
        $backoutRepat = $deductions->where('account', AgentDeduction::ACCOUNT_DEDUCTION)->values();
        $payments     = $deductions->where('account', AgentDeduction::ACCOUNT_PAID)->values();

        $totalBackoutRepat = (float) $backoutRepat->sum('amount');
        $totalPayment      = (float) $payments->sum('amount');

        // Receivables: Receivable module, account 'Agents & Applicant Payment', status RECEIVED.
        $receivables = Receivable::query()
            ->where('agent_id', $agent->id)
            ->where('account', 'Agents & Applicant Payment')
            ->where('status', Receivable::STATUS_RECEIVED)
            ->with(['applicant', 'agent'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
        $totalReceivables = (float) $receivables->sum('amount');

        return [
            'agent'              => $agent->load('branch'),
            'releasedCommission' => $releasedCommission,
            'cashAdvance'        => $cashAdvance,
            'backoutRepat'       => $backoutRepat,
            'receivables'        => $receivables,
            'payments'           => $payments,
            'totalCommission'    => $totalCommission,
            'totalCashAdvance'   => $totalCashAdvance,
            'totalBackoutRepat'  => $totalBackoutRepat,
            'totalReceivables'   => $totalReceivables,
            'totalPayment'       => $totalPayment,
            'balance'            => $totalCashAdvance + $totalBackoutRepat - $totalReceivables - $totalPayment,
            'usdToPhp'           => $converter->usdToPhpRate(),
        ];
    }

    /**
     * Per-agent ledger rows — Agent Ledger spec. Mirrors agentLedgerData()
     * (the single-agent view you land on when clicking an agent name) so the
     * summary table and the agent's own ledger always agree (Cyd 2026-09-26):
     *  - commission    : RELEASED agent-charged items on commission accounts
     *                    (Partial/Full/Fit to work/Contract/Deployed/Return);
     *                    Return rows are shown on the agent ledger but excluded
     *                    from Total Commission.
     *  - cash_advance  : RELEASED agent-charged items on cash-advance accounts
     *  - backout_repat : AgentDeduction rows (account = Deduction)
     *  - receivable    : received Receivable rows (account = Agents & Applicant Payment)
     *  - payments      : AgentDeduction rows (account = Paid)
     *  - balance       = cash_advance + backout_repat − receivable − payments
     *
     * NOTE: previously this counted PENDING expense items only, so any entry
     * that had already been Released showed as ₱0 here even though it appeared
     * in the agent's own ledger. Now both paths read the same RELEASED data.
     */
    private function buildReportRows($agents, CurrencyConverter $converter, ?int $agentId = null)
    {
        $agencyId = auth()->user()->agency_id;

        $commissionAccountIds = Account::where('agency_id', $agencyId)
            ->whereIn('name', self::RECEIVABLE_ACCOUNT_NAMES)
            ->pluck('id');
        $returnAccountIds = Account::where('agency_id', $agencyId)
            ->where('name', 'Return')
            ->pluck('id');
        $cashAdvanceAccountIds = Account::where('agency_id', $agencyId)
            ->whereIn('name', self::CASH_ADVANCE_ACCOUNT_NAMES)
            ->pluck('id');

        // Commission + Cash Advance: RELEASED agent-charged expense items.
        $items = $this->scopeQueryAgentsByBranch(ExpenseRequestItem::query())
            ->where('charge', 'agent')
            ->whereNotNull('agent_id')
            ->whereHas('expenseRequest', fn ($er) => $er->where('agency_id', $agencyId)->where('status', ExpenseRequest::STATUS_RELEASED))
            ->get(['id', 'agent_id', 'account_id', 'currency', 'amount']);

        $commission = [];
        $cashAdvance = [];
        foreach ($items as $item) {
            $agentKey = (int) $item->agent_id;
            $amount = $converter->toPhp((float) $item->amount, $item->currency);

            if ($cashAdvanceAccountIds->contains($item->account_id)) {
                $cashAdvance[$agentKey] = ($cashAdvance[$agentKey] ?? 0.0) + $amount;
            } elseif ($commissionAccountIds->contains($item->account_id) && ! $returnAccountIds->contains($item->account_id)) {
                $commission[$agentKey] = ($commission[$agentKey] ?? 0.0) + $amount;
            }
        }

        // Backout & Repat (Deduction) + Payments (Paid): AgentDeduction rows.
        $dedAgg = $this->scopeQueryAgentsByBranch(AgentDeduction::query())
            ->where('agency_id', $agencyId)
            ->whereNotNull('agent_id')
            ->select('agent_id', 'account')
            ->selectRaw('COALESCE(SUM(amount),0) as total')
            ->groupBy('agent_id', 'account')
            ->get();

        $backoutByAgent = [];
        $paidByAgent = [];
        foreach ($dedAgg as $d) {
            $key = (int) $d->agent_id;
            if ($d->account === AgentDeduction::ACCOUNT_DEDUCTION) {
                $backoutByAgent[$key] = (float) $d->total;
            } elseif ($d->account === AgentDeduction::ACCOUNT_PAID) {
                $paidByAgent[$key] = (float) $d->total;
            }
        }

        // Receivables: received Receivable rows on the agents-payment account.
        $recAgg = $this->scopeQueryAgentsByBranch(Receivable::query())
            ->where('agency_id', $agencyId)
            ->whereNotNull('agent_id')
            ->where('account', 'Agents & Applicant Payment')
            ->where('status', Receivable::STATUS_RECEIVED)
            ->select('agent_id')
            ->selectRaw('COALESCE(SUM(amount),0) as total')
            ->groupBy('agent_id')
            ->get()
            ->keyBy('agent_id');

        return $agents
            ->filter(fn (Agent $a) => $agentId === null || (int) $a->id === (int) $agentId)
            ->map(function (Agent $agent) use ($commission, $cashAdvance, $backoutByAgent, $paidByAgent, $recAgg) {
                $key = (int) $agent->id;
                $comm    = (float) ($commission[$key] ?? 0);
                $ca      = (float) ($cashAdvance[$key] ?? 0);
                $backout = (float) ($backoutByAgent[$key] ?? 0);
                $paid    = (float) ($paidByAgent[$key] ?? 0);
                $recv    = (float) ($recAgg->get($agent->id)->total ?? 0);

                return [
                    'id'            => $agent->id,
                    'agent'         => $agent->name,
                    'branch'        => $agent->branch?->name ?? '-',
                    'commission'    => $comm,
                    'cash_advance'  => $ca,
                    'backout_repat' => $backout,
                    'receivable'    => $recv,
                    'payments'      => $paid,
                    'balance'       => $ca + $backout - $recv - $paid,
                ];
            })
            ->sortBy('agent')
            ->values();
    }

    /**
     * Shared agent/applicant dropdown lists for the new-entry forms.
     */
    private function agentApplicantLists(): array
    {
        $agencyId = auth()->user()->agency_id;

        $agents = $this->scopeAgentsByBranch(Agent::where('agency_id', $agencyId))
            ->with('branch')
            ->orderBy('name')
            ->get();

        // (Cyd 2026-10-23) Applicants follow the Agent list: a branch account
        // only sees applicants whose agent is in its own branch (or a
        // shared/main-office agent), so an applicant only shows under its agent.
        $applicants = $this->scopeApplicantsByBranch(Applicant::where('agency_id', $agencyId))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'agent_id', 'first_name', 'last_name']);

        return [$agents, $applicants];
    }

    /**
     * Agency isolation guard (404 on mismatch), matching Receivable pattern.
     */
    private function authorizeAgency(int $recordAgencyId): void
    {
        if ((int) $recordAgencyId !== (int) auth()->user()->agency_id) {
            abort(404);
        }
    }

    /**
     * Sum expense items, converting USD amounts to PHP via the converter.
     */
    private function sumItems($items, CurrencyConverter $converter): float
    {
        return (float) $items->sum(fn ($item) => $converter->toPhp((float) $item->amount, $item->currency));
    }
    /**
     * (Cyd 2026-09-28 #3) Main Office accounts must see ALL agents — they are
     * head-office users, not branch-scoped. Only non-main-office branch
     * accounts stay scoped to their own branch (+ shared/main-office agents).
     */
    private function ledgerBranchLocked(): bool
    {
        $user = auth()->user();

        return $user && $user->isBranchLocked() && ! $user->isMainOffice();
    }

    /**
     * (Branch feature) Branch-locked users may only work with agents of their
     * own branch plus shared/main-office agents (branch_id null) — the same
     * convention used by the Receivable create form.
     */
    private function branchLockedAgentCondition($query): void
    {
        $user = auth()->user();
        $query->where(function ($q) use ($user) {
            $q->where('branch_id', $user->branch_id)
              ->orWhereNull('branch_id');
        });
    }

    /** Applicants follow the Agent branch scope (branch users only). */
    private function scopeApplicantsByBranch($query)
    {
        return $query->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)));
    }

    private function scopeAgentsByBranch($query)
    {
        return $query->when($this->ledgerBranchLocked(), fn ($q) => $q->where(function ($w) {
            $w->where('branch_id', auth()->user()->branch_id)
              ->orWhereNull('branch_id');
        }));
    }

    /** For models that relate to agents via an agent_id column. */
    private function scopeQueryAgentsByBranch($query)
    {
        return $query->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)));
    }

    /** 404 when a branch-locked user touches an agent outside their scope. */
    private function authorizeAgentBranch(?Agent $agent): void
    {
        $user = auth()->user();
        if (! $user || ! $this->ledgerBranchLocked()) {
            return;
        }
        if (! $agent || ((int) $agent->branch_id !== (int) $user->branch_id && $agent->branch_id !== null)) {
            abort(404);
        }
    }

}
