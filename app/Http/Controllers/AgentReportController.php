<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\AgentPreviousPayment;
use App\Models\Applicant;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestStatusHistory;
use App\Models\ExpenseRequestItem;
use App\Models\Receivable;
use App\Models\StartingBalance;
use App\Services\CurrencyConverter;
use App\Services\TransactionEditLog;
use App\Support\ModuleAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AgentReportController extends Controller
{
    /** Withdrawn/Repat statuses: 35 Repatriated, 38 Cancel, 50 Backout. */
    private const BACKOUT_STATUSES = [35, 38, 50];

    /** Agent Report tabs (Return Payment tab added per Cyd 2026-11-04, card 129). */
    private const TABS = ['commission', 'cash-advance', 'receivables', 'return-payment', 'payments', 'previous-payments', 'deductions', 'starting-balances', 'report'];

    /**
     * The tab this screen opens on.
     *
     * The Commission tab was removed by aa87fe4b (card 6afae127) and restored by
     * Josh's decision of 2026-11-20 on the same card — but only the TAB comes
     * back. The default stays the Agent Ledger: that is what this screen is, and
     * landing on Commission would open an empty "No commission transactions
     * yet." page for agencies with no commission data.
     */
    private const DEFAULT_TAB = 'report';

    /** Account names that route to the Receivables tab (moved out of Commission). */
    private const RECEIVABLE_ACCOUNT_NAMES = ['Partial', 'Full', 'Fit to work', 'Contract', 'Deployed', 'Return'];

    /**
     * The one Receivable-module account the Receivables tab shows (card
     * 6afb09e9). Note this is a `receivables.account` value, not an
     * `accounts.name` — the two vocabularies have nothing in common, which is
     * why RECEIVABLE_ACCOUNT_NAMES above cannot be reused here.
     */
    private const RECEIVABLE_AR_ACCOUNT = 'Agents & Applicant Payment';

    /** Released expenses shown as their own block on the Commission tab (Cyd 2026-11-01). */
    private const RELEASED_COMMISSION_ACCOUNT_NAMES = ['Fit to work', 'Deployed'];

    /** Account names that route to the Cash Advance tab (moved out of Commission). */
    private const CASH_ADVANCE_ACCOUNT_NAMES = ['Cash advance', 'Agent Advances'];

    /**
     * 5-tab Agents Report page.
     *
     * Tabs (card 6afae127: the Commission tab was removed by aa87fe4b and
     * restored on Josh's 2026-11-20 decision; DEFAULT_TAB stays 'report'):
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

        // A stale ?tab=commission bookmark falls back to DEFAULT_TAB, not to
        // TABS[0] — landing on 'cash-advance' would look like a different bug.
        $tab = $request->input('tab', self::DEFAULT_TAB);
        if (! in_array($tab, self::TABS, true)) {
            $tab = self::DEFAULT_TAB;
        }
        // Gulf only (card 6b056b38): a typed ?tab=previous-payments on FINAS/
        // CSDI falls back to the default tab exactly like an unknown one.
        if ($tab === 'previous-payments' && ! ModuleAccess::gulfOnly($agencyId)) {
            $tab = self::DEFAULT_TAB;
        }
        $agentId = $request->input('agent_id');
        $this->authorizeAgentFilter($agentId);

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
        // Restored by Josh's 2026-11-20 decision on card 6afae127 (revert of the
        // aa87fe4b removal). Identical to the pre-removal query: only PENDING
        // requests show here; approved items become Paid entries in the
        // Deductions & Paid tab (Toybits 2026-08-29).
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

        // ---- Tab 1b: Released commission expenses (Cyd 2026-11-01) ----
        // Also restored with the tab. "Display all released Expenses with
        // Account: Fit to work, Deployed" — those rows never showed anywhere:
        // the Receivables tab lists the Receivable module, and the Report tab
        // only aggregates them.
        $releasedCommissionAccountIds = Account::where('agency_id', $agencyId)
            ->whereIn('name', self::RELEASED_COMMISSION_ACCOUNT_NAMES)
            ->pluck('id');

        $releasedCommissionItems = ExpenseRequestItem::query()
            ->where('charge', 'agent')
            ->whereNotNull('agent_id')
            ->whereIn('account_id', $releasedCommissionAccountIds)
            ->whereHas('expenseRequest', fn ($er) => $er->where('agency_id', $agencyId)->where('status', ExpenseRequest::STATUS_RELEASED))
            ->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)))
            ->with(['expenseRequest.user', 'expenseRequest.branch', 'applicant', 'agent', 'account', 'country'])
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->orderByDesc('created_at')
            ->get();

        $releasedCommissionTotal = $releasedCommissionItems
            ->sum(fn ($item) => $converter->toPhp((float) $item->amount, $item->currency));

        // ---- Tab 1c: Cash Advance (agent-charged items on Cash Advance accounts) ----
        // (Josh 2026-11-09) "All RELEASED status with CASH ADVANCE Account should
        // also be posted on the AGENTS REPORT > CASH ADVANCE Tab."
        // This tab used to list PENDING rows only, which contradicted the Agent
        // Ledger: `agentLedgerData()` has always summed RELEASED cash advances
        // into the balance, so an agent's balance counted rows this tab never
        // showed. Released is included here; cancelled stays out.
        $cashAdvanceItems = ExpenseRequestItem::query()
            ->where('charge', 'agent')
            ->whereNotNull('agent_id')
            ->whereHas('expenseRequest', fn ($er) => $er
                ->where('agency_id', $agencyId)
                ->whereIn('status', [ExpenseRequest::STATUS_PENDING, ExpenseRequest::STATUS_RELEASED]))
            ->when($cashAdvanceAccountIds->isNotEmpty(), fn ($q) => $q->whereIn('account_id', $cashAdvanceAccountIds))
            ->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)))
            ->with(['expenseRequest.user', 'expenseRequest.branch', 'applicant', 'agent', 'account', 'country'])
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            // Pending first: those are the rows still needing action. Released
            // cash advances follow, newest first, so the tab does not bury the
            // open items under 700+ historical rows.
            ->join('expense_requests as ca_er', 'ca_er.id', '=', 'expense_request_items.expense_request_id')
            ->orderByRaw("CASE WHEN ca_er.status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('ca_er.date')
            ->orderByDesc('expense_request_items.id')
            ->select('expense_request_items.*')
            ->get();

        // ---- Tab 1c: Receivables (Receivable module, Agents & Applicant Payment, RECEIVED) ----
        //
        // (Card 6afb09e9, Cyd 2026-11-16) This tab used to list PENDING
        // agent-charged rows from the Expenses & Payments module. That was the
        // wrong table: those rows are expense requests, and displaying them here
        // put the same transaction on two screens while showing nothing that the
        // Receivable module actually owns. The tab is now a read-only view of
        // the Receivable module's own rows, on the one account the card names,
        // in the one status that means money was collected.
        //
        // It also makes the tab agree with the Agent Ledger: buildReportRows()
        // and agentLedgerData() have always computed the receivable (AR) figure
        // from exactly this query, so the summary and the detail finally read
        // the same records.
        //
        // Read-only by design — no expense request is written, migrated or
        // backfilled. An expense request is not a receivable and inventing one
        // would put a number in the books with no source document behind it.
        $receivableItems = Receivable::query()
            ->where('agency_id', $agencyId)
            ->whereNotNull('agent_id')
            ->where('account', self::RECEIVABLE_AR_ACCOUNT)
            ->where('status', Receivable::STATUS_RECEIVED)
            ->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)))
            ->with(['applicant', 'agent', 'encoder'])
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        // ---- Tab 1d: Return Payment (Cyd 2026-11-04) ----
        // Expenses charged to the agent on the "Return" account: money handed
        // back to the agent. Listed on its own tab and added to the agent's
        // balance. RELEASED only, matching the Agent Ledger totals below.
        $returnAccountIds = Account::where('agency_id', $agencyId)
            ->where('name', 'Return')
            ->pluck('id');

        $returnPaymentItems = ExpenseRequestItem::query()
            ->where('charge', 'agent')
            ->whereNotNull('agent_id')
            ->whereIn('account_id', $returnAccountIds)
            ->whereHas('expenseRequest', fn ($er) => $er->where('agency_id', $agencyId)->where('status', ExpenseRequest::STATUS_RELEASED))
            ->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)))
            ->with(['expenseRequest.user', 'expenseRequest.branch', 'applicant', 'agent', 'account'])
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->orderByDesc('id')
            ->get();

        $returnPaymentTotal = $returnPaymentItems
            ->sum(fn ($item) => $converter->toPhp((float) $item->amount, $item->currency));

        // ---- Tab 3: Deductions (AgentDeduction rows, account = Deduction) ----
        // Cyd 2026-10-23: Payments and Deductions are separate tabs, so this one
        // no longer mixes the approval-generated "Paid" rows in with deductions.
        $deductions = AgentDeduction::query()
            ->where('agency_id', $agencyId)
            ->where('account', AgentDeduction::ACCOUNT_DEDUCTION)
            ->with(['agent', 'applicant', 'encoder'])
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        // ---- Tab 2: Payments (Expenses & Payments module only) ----
        // Payments = the "Payment / already paid" amounts recorded on expense
        // transactions in the Expenses & Payments module. Receivable-module rows
        // used to be listed here, so batch-uploaded receivables showed up as
        // payments (Cyd 2026-10-23).
        $payments = ExpenseRequestItem::query()
            ->where('payment', '>', 0)
            ->whereHas('expenseRequest', fn ($er) => $er->where('agency_id', $agencyId))
            ->with(['expenseRequest.user', 'expenseRequest.branch', 'applicant', 'agent', 'account'])
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)))
            ->orderByDesc('id')
            ->get();

        // ---- Tab 2b: Previous Payment (Gulf only, card 6b056b38) ----
        // Payments made before go-live, uploaded as history. Reads ONLY the new
        // table — never loops over expense items. No `payment > 0` filter: an
        // amount of 0 is a valid imported row and must stay visible.
        $previousPayments = collect();
        $previousPaymentsTotal = 0.0;
        if (ModuleAccess::gulfOnly($agencyId)) {
            $previousPayments = AgentPreviousPayment::query()
                ->where('agency_id', $agencyId)
                ->with(['agent', 'applicant', 'encoder'])
                ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
                ->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)))
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();

            $previousPaymentsTotal = $previousPayments
                ->sum(fn ($p) => $converter->toPhp((float) $p->amount, $p->currency));
        }

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
            'commission'     => $reportRows->sum('commission'),
            'cash_advance'   => $reportRows->sum('cash_advance'),
            'backout_repat'  => $reportRows->sum('backout_repat'),
            'receivable'     => $reportRows->sum('receivable'),
            'return_payment' => $reportRows->sum('return_payment'),
            'payments'       => $reportRows->sum('payments'),
            'balance'        => $reportRows->sum('balance'),
        ];

        // ---- Tab totals (card 6b056f91, Cyd 2026-11-24) ----
        // Cyd wants the total amount visible on each data tab without scrolling
        // to the footer of a long table. One source of truth: every tab total is
        // the sum of the rows that tab actually shows, converted to PHP with the
        // same CurrencyConverter the rows use. No view may sum money — the two
        // sums that used to live in blades (_deductions tfoot, and the Return
        // Payment badge/footer which already read a controller variable) read
        // from here now.
        //
        // The totals follow the Agent filter because they sum the already-filtered
        // collections. Commission/released/return/payments sums mirror exactly
        // what the existing footers did (gross `amount`, not netAmount()).
        $phpTotal = fn ($collection, string $column = 'amount') => $collection->sum(
            fn ($row) => $converter->toPhp((float) $row->{$column}, (string) ($row->currency ?: 'PHP'))
        );

        $tabTotals = [
            'commission'          => $phpTotal($commissionItems),
            'released_commission' => $releasedCommissionTotal,
            'cash_advance'        => $phpTotal($cashAdvanceItems),
            // Receivable module amounts are PHP only (no currency column).
            'receivables'         => (float) $receivableItems->sum(fn ($r) => (float) $r->amount),
            'return_payment'      => $returnPaymentTotal,
            // AgentDeduction amounts are PHP only.
            'deductions'          => (float) $deductions->sum(fn ($d) => (float) $d->amount),
            'payments'            => $phpTotal($payments, 'payment'),
            'previous_payments'   => $previousPaymentsTotal,
        ];

        return view('agent_report.index', compact(
            'tab',
            'agents',
            'agentId',
            'commissionItems',
            'releasedCommissionItems',
            'releasedCommissionTotal',
            'cashAdvanceItems',
            'receivableItems',
            'returnPaymentItems',
            'returnPaymentTotal',
            'payments',
            'previousPayments',
            'previousPaymentsTotal',
            'deductions',
            'startingBalances',
            'reportRows',
            'totals',
            'converter'
        ) + [
            // Card 6b056b38: the Previous Payment tab is gulf-gated IN THE VIEW,
            // so the view needs the id. Passing it explicitly rather than reading
            // auth() inside the template (Bitoy fix, 2026-11-24 12:32 — the first
            // attempt used a variable index() never passed, which 500'd
            // /agents-report for a logged-in Gulf admin).
            'agencyId' => $agencyId,
            // Card 6b056f91: per-tab totals for the sticky summary lines.
            'tabTotals' => $tabTotals,
            'canManageDeductions' => $this->canManageDeductions(),
            'canManagePayments'   => $this->canManagePayments(),
            // Cyd 2026-11-01 (card 124): only Admin may delete Deduction/Payment rows.
            'canDeleteTransactions' => in_array((string) auth()->user()->user_type, ['super_admin', 'admin'], true),
            // Card 6afb09e9: the Receivables tab links out to receivable.show.
            // That route has its own agency + branch guards and would 404 on a
            // row this user cannot see, so the link is offered only to the
            // roles that already see every row on this screen.
            'canViewReceivable' => auth()->user()->seesAllAccountingData(),
        ]);
    }

    /**
     * (Cyd 2026-09-26) Admin (super_admin/admin) and Accounting (billing) may
     * create Deduction transactions; everyone else gets the tab without the
     * create/bulk buttons and a 403 on the create/store/bulk routes.
     *
     * (Josh @ LANDAS 2026-10-23) "wala xa sa admin side, meron sa accounting" —
     * the buttons were hidden for plain Admin accounts. Admin is now included
     * on purpose, alongside the privileged accounts (Mae, Evelyn, Angel) that
     * Cyd listed on 2026-09-28.
     */
    private function canManageDeductions(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->isPrivileged()
            || in_array((string) $user->user_type, ['super_admin', 'admin', 'billing'], true);
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
     * Edit an existing deduction transaction (card 6af170f5, Josh 2026-11-11:
     * "place review on the transaction for Admin and Accounting Access only so
     * they can edit").
     *
     * Reuses canManageDeductions() — the same Admin / Accounting / privileged
     * gate that already governs creating a deduction, so the person fixing a
     * bad particular is the same class of user who could have encoded it.
     * Branch users still cannot cross their branch boundary.
     */
    public function deductionEdit(AgentDeduction $agentDeduction): View
    {
        abort_unless($this->canManageDeductions(), 403, 'Only Admin and Accounting can edit deduction transactions.');

        $this->authorizeAgency((int) $agentDeduction->agency_id);
        $this->authorizeAgentBranch($agentDeduction->agent);

        [$agents, $applicants] = $this->agentApplicantLists();

        return view('agent_report.deduction_edit', [
            'deduction'  => $agentDeduction->load(['agent', 'applicant', 'encoder']),
            'agents'     => $agents,
            'applicants' => $applicants,
        ]);
    }

    /**
     * Persist an edit to a deduction transaction (card 6af170f5).
     */
    public function deductionUpdate(Request $request, AgentDeduction $agentDeduction): RedirectResponse
    {
        abort_unless($this->canManageDeductions(), 403, 'Only Admin and Accounting can edit deduction transactions.');

        $this->authorizeAgency((int) $agentDeduction->agency_id);
        $this->authorizeAgentBranch($agentDeduction->agent);

        $agencyId = auth()->user()->agency_id;

        $validated = $request->validate([
            'date'       => ['required', 'date'],
            'amount'     => ['required', 'numeric', 'min:0.01'],
            'particular' => ['nullable', 'string', 'max:2000'],
        ]);

        // Only the fields Accounting/Admin are expected to correct. The agent,
        // applicant and account stay as encoded — changing ownership of a
        // posted transaction is a different, riskier operation.
        $agentDeduction->fill([
            'date'       => $validated['date'],
            'amount'     => $validated['amount'],
            'particular' => $validated['particular'] ?? null,
        ]);
        $agentDeduction->save();

        return redirect()->route('agent_report.index', ['tab' => 'deductions'])
            ->with('success', 'Deduction transaction updated.');
    }

    /**
     * Bulk upload page for Deductions (Cyd 2026-10-23): one CSV row = one
     * deduction transaction (Date, Agent, Applicant, Amount, Particular).
     */
    public function deductionBulkUpload(): View
    {
        abort_unless($this->canManageDeductions(), 403, 'Only Admin and Accounting can create deduction transactions.');

        return view('agent_report.deduction_bulk');
    }

    /**
     * CSV template for the deduction bulk upload.
     */
    public function deductionBulkTemplate()
    {
        $headers = ['Date', agent_header('Agent'), 'Applicant', 'Amount', 'Particular/Description'];
        $today = now()->toDateString();
        $sample = [
            [$today, 'Juan Dela Cruz', 'Dela Cruz, Maria', '1500.00', 'Cash advance balance deduction'],
            [$today, 'Juan Dela Cruz', '', '250.00', 'Uniform deduction'],
        ];

        return response()->streamDownload(function () use ($headers, $sample) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($sample as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'deductions-template.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Import the deduction CSV. All-or-nothing: every row is validated first and
     * nothing is saved when any row has a problem (same contract as the expense
     * bulk upload, so a half-imported sheet can never happen).
     */
    public function deductionBulkImport(Request $request): RedirectResponse
    {
        abort_unless($this->canManageDeductions(), 403, 'Only Admin and Accounting can create deduction transactions.');

        $agencyId = auth()->user()->agency_id;

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $rows = $this->parseDeductionCsv($request->file('csv_file')->getRealPath());
        if (is_string($rows)) {
            return back()->withErrors(['csv_file' => $rows]);
        }

        $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));

        $agentByName = [];
        foreach ($this->scopeAgentsByBranch(Agent::where('agency_id', $agencyId))->get(['id', 'branch_id', 'name']) as $agent) {
            $agentByName[$norm($agent->name)][] = $agent;
        }

        $applicantByAgent = [];
        foreach (Applicant::where('agency_id', $agencyId)->get(['id', 'agent_id', 'first_name', 'middle_name', 'last_name', 'suffix']) as $applicant) {
            $applicantByAgent[(int) $applicant->agent_id][$norm($applicant->full_name)] = $applicant;
            $applicantByAgent[(int) $applicant->agent_id][$norm($applicant->last_name . ', ' . $applicant->first_name)] = $applicant;
        }

        $errors = [];
        $validatedRows = [];

        foreach ($rows as $row) {
            $line = $row['line'];
            $d = $row['data'];
            $rowErrors = [];

            // ---- Date (blank = today) ----
            $date = now()->toDateString();
            $dateRaw = trim((string) ($d['date'] ?? ''));
            if ($dateRaw !== '') {
                $ts = strtotime($dateRaw);
                if ($ts === false) {
                    $rowErrors[] = "Date '{$dateRaw}' is not a valid date.";
                } else {
                    $date = date('Y-m-d', $ts);
                }
            }

            // ---- Agent (required, must belong to the agency/branch) ----
            $agent = null;
            $agentRaw = trim((string) ($d['agent'] ?? ''));
            if ($agentRaw === '') {
                $rowErrors[] = 'Agent is required.';
            } else {
                $matches = $agentByName[$norm($agentRaw)] ?? [];
                if (count($matches) === 0) {
                    $rowErrors[] = "Agent '{$agentRaw}' was not found.";
                } elseif (count($matches) > 1) {
                    $rowErrors[] = "Agent '{$agentRaw}' matches more than one agent — use the full name.";
                } else {
                    $agent = $matches[0];
                }
            }

            // ---- Applicant (optional, must belong to the chosen agent) ----
            $applicant = null;
            $applicantRaw = trim((string) ($d['applicant'] ?? ''));
            if ($applicantRaw !== '' && $agent) {
                $applicant = $applicantByAgent[(int) $agent->id][$norm($applicantRaw)] ?? null;
                if (! $applicant) {
                    $rowErrors[] = "Applicant '{$applicantRaw}' does not belong to agent '{$agent->name}'.";
                }
            } elseif ($applicantRaw !== '' && ! $agent) {
                $rowErrors[] = 'Applicant needs a valid Agent in the same row.';
            }

            // ---- Amount (required, > 0) ----
            $amountRaw = trim((string) ($d['amount'] ?? ''));
            $amountRaw = str_replace([',', '₱', ' '], '', $amountRaw);
            $amount = null;
            if ($amountRaw === '') {
                $rowErrors[] = 'Amount is required.';
            } elseif (! is_numeric($amountRaw)) {
                $rowErrors[] = "Amount '{$amountRaw}' must be a number.";
            } elseif ((float) $amountRaw <= 0) {
                $rowErrors[] = 'Amount must be greater than zero.';
            } else {
                $amount = round((float) $amountRaw, 2);
            }

            $particular = trim((string) ($d['particular'] ?? '')) ?: null;

            // No duplicate gate on deduction uploads (Cyd 2026-11-06): rows that
            // merely look alike — same agent, date, amount and description — are
            // legitimate entries and must import.

            if ($rowErrors) {
                $errors[$line] = $rowErrors;
                continue;
            }

            $validatedRows[] = [
                'agency_id'    => $agencyId,
                'user_id'      => auth()->id(),
                'agent_id'     => $agent->id,
                'applicant_id' => $applicant?->id,
                'date'         => $date,
                'account'      => AgentDeduction::ACCOUNT_DEDUCTION,
                'amount'       => $amount,
                'particular'   => $particular,
            ];
        }

        if (! empty($errors)) {
            return back()->withErrors(['csv_file' => 'No rows were imported — ' . count($errors) . ' row(s) need fixing.'])
                ->with('bulk_errors', $errors)
                ->withInput();
        }

        DB::transaction(function () use ($validatedRows) {
            foreach ($validatedRows as $row) {
                AgentDeduction::create($row);
            }
        });

        return redirect()->route('agent_report.index', ['tab' => 'deductions'])
            ->with('success', 'Imported ' . count($validatedRows) . ' deduction transaction(s).');
    }

    /**
     * Parse the deduction CSV into [{line, data}] with normalized header keys.
     * Accepts comma/semicolon/tab separators, BOM/UTF-16 (Excel), and any column
     * order — the header row is matched by name.
     */
    /**
     * Normalises a CSV header cell for alias lookup.
     *
     * Lowercases, collapses whitespace, and — the reason this exists (card
     * 6af170f5) — strips the spaces people put around separators, so
     * "PARTICULAR / DESCRIPTION" (the client's own template header) reduces to
     * "particular/description" and matches the alias instead of being read as
     * an unknown column and losing every row's text.
     */
    private function normaliseHeaderKey(string $header): string
    {
        $key = mb_strtolower(preg_replace('/\s+/', ' ', trim($header)));

        // "particular / description" -> "particular/description"
        $key = preg_replace('/\s*([\/\\|])\s*/', '$1', $key);

        return (string) $key;
    }

    private function parseDeductionCsv(string $path)
    {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return 'Could not read the uploaded file.';
        }

        if (str_starts_with($raw, "\xFF\xFE")) {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16LE');
        } elseif (str_starts_with($raw, "\xFE\xFF")) {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16BE');
        } elseif (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }
        if ($raw !== '' && ! mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');
        }
        if (trim($raw) === '') {
            return 'The file is empty.';
        }

        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        $aliases = [
            'date'         => 'date',
            'transaction date' => 'date',
            'agent'        => 'agent',
            'agent name'   => 'agent',
            'applicant'    => 'applicant',
            'applicant name' => 'applicant',
            'amount'       => 'amount',
            'particular'   => 'particular',
            'particular/description' => 'particular',
            'description'  => 'particular',
            'particulars'  => 'particular',
            'remarks'      => 'particular',
            'remark'       => 'particular',
            'notes'        => 'particular',
            'note'         => 'particular',
            'reason'       => 'particular',
            'narration'    => 'particular',
            'details'      => 'particular',
            'detail'       => 'particular',
            'purpose'      => 'particular',
        ];

        $parse = function (string $delimiter) use ($raw, $aliases) {
            $handle = fopen('php://temp', 'r+');
            fwrite($handle, $raw);
            rewind($handle);

            $parsed = [];
            while (($cells = fgetcsv($handle, 0, $delimiter)) !== false) {
                $parsed[] = $cells;
            }
            fclose($handle);

            if (empty($parsed)) {
                return 'The file has no readable rows.';
            }

            $headerIndex = null;
            $fieldByIndex = [];
            $unmapped = [];
            foreach (array_slice($parsed, 0, 30, true) as $i => $cells) {
                if (empty(array_filter(array_map('trim', $cells), fn ($c) => $c !== ''))) {
                    continue;
                }
                $candidate = [];
                foreach ($cells as $idx => $header) {
                    $key = $this->normaliseHeaderKey((string) $header);
                    $field = $aliases[$key] ?? null;
                    if ($field && ! in_array($field, $candidate, true)) {
                        $candidate[$idx] = $field;
                    }
                }
                if (in_array('agent', $candidate, true) && in_array('amount', $candidate, true)) {
                    $headerIndex = $i;
                    $fieldByIndex = $candidate;
                    // Columns that look like the description but are not mapped:
                    // importing them would silently store every row with a blank
                    // particular (the 2026-11-07 data loss).
                    foreach ($cells as $idx => $header) {
                        if (isset($candidate[$idx])) {
                            continue;
                        }
                        $key = $this->normaliseHeaderKey((string) $header);
                        if ($key !== '' && preg_match('/particular|descri|remark|note|reason|narrat|deta?il|purpose|comment|memo|explanation|justificat|salary/', $key)) {
                            $unmapped[] = trim((string) $header);
                            continue;
                        }
                        // A free-text column sitting where the description usually
                        // lives (4th/5th) is almost certainly meant to be one.
                        if ($idx >= 3 && ! preg_match('/^(branch|country|position|currency|status|ref|reference|date|agent|applicant|amount|#)/', $key)) {
                            $unmapped[] = trim((string) $header);
                        }
                    }
                    break;
                }
            }

            if (! empty($unmapped)) {
                return 'The file has a column this uploader does not recognise: '
                    . implode(', ', array_unique($unmapped))
                    . '. Rename it to Particular/Description so the text is not lost.';
            }

            if ($headerIndex === null) {
                return 'No header row found. The first row must name the columns: Date, Agent, Applicant, Amount, Particular/Description.';
            }

            $rows = [];
            $lineNo = $headerIndex + 1;
            foreach (array_slice($parsed, $headerIndex + 1, null, true) as $i => $cells) {
                $lineNo = $i + 1;
                if (empty(array_filter(array_map('trim', $cells), fn ($c) => $c !== ''))) {
                    continue;
                }
                $data = [];
                foreach ($fieldByIndex as $idx => $field) {
                    $data[$field] = $cells[$idx] ?? '';
                }
                $rows[] = ['line' => $lineNo, 'data' => $data];
            }

            if (empty($rows)) {
                return 'The file has no data rows — add at least one deduction below the header.';
            }

            if (count($rows) > 2000) {
                return 'The file has too many rows (' . count($rows) . '). Split it into batches of 2000 or fewer.';
            }

            return $rows;
        };

        $lastError = null;
        foreach ([',', ';', "\t"] as $delimiter) {
            $result = $parse($delimiter);
            if (is_array($result)) {
                return $result;
            }
            // A recognised header with an unrecognised description column is a
            // real, actionable error — do not let a later delimiter pass
            // overwrite it with a vaguer message.
            if (str_contains((string) $result, 'does not recognise')) {
                return $result;
            }
            $lastError = $result;
        }

        return $lastError ?? 'Could not read the file.';
    }

    // ------------------------------------------------------------------
    // Tab 2: Payments — bulk upload (Cyd 2026-11-01)
    // ------------------------------------------------------------------

    /**
     * Bulk upload page for Payments: one CSV row = the payment amount recorded
     * on an existing Expenses & Payments line (matched by Ref# + Applicant /
     * Particular). Payments are only ever applied to transactions that already
     * exist — this does not create expense requests.
     */
    public function paymentBulkUpload(): View
    {
        abort_unless($this->canManagePayments(), 403, 'Only Admin and Accounting can record payments.');

        return view('agent_report.payment_bulk');
    }

    /**
     * CSV template for the payment bulk upload.
     */
    public function paymentBulkTemplate()
    {
        // Josh 2026-11-02 (card 124): the Ref# column is gone — a row is
        // identified by the transaction *Date* instead.
        $headers = ['Date', 'Applicant', agent_header('Agent'), 'Currency', 'Payment', 'Particular'];
        $sample = [
            [now()->toDateString(), 'Dela Cruz, Maria', 'Juan Dela Cruz', 'PHP', '1500.00', 'FTW Commission'],
            [now()->toDateString(), '', '', 'PHP', '250.00', ''],
        ];

        return response()->streamDownload(function () use ($headers, $sample) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($sample as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'payments-template.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Apply the payment CSV. All-or-nothing: every row is validated first, and a
     * single bad row leaves every transaction untouched.
     */
    public function paymentBulkImport(Request $request): RedirectResponse
    {
        abort_unless($this->canManagePayments(), 403, 'Only Admin and Accounting can record payments.');

        $agencyId = (int) auth()->user()->agency_id;

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $rows = $this->parsePaymentCsv($request->file('csv_file')->getRealPath());
        if (is_string($rows)) {
            return back()->withErrors(['csv_file' => $rows]);
        }

        $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));

        // Transactions indexed by Ref# and by Date (agency scoped). Date is the
        // primary key of the template; Ref# is still accepted for old files.
        $requestsByRef = [];
        $requestsByDate = [];
        foreach (ExpenseRequest::where('agency_id', $agencyId)
                     ->with(['items.applicant', 'items.agent', 'items.account'])
                     ->get() as $expenseRequest) {
            $ref = $norm($expenseRequest->reference_no);
            if ($ref !== '') {
                $requestsByRef[$ref][] = $expenseRequest;
            }
            $day = optional($expenseRequest->date)->toDateString();
            if ($day) {
                $requestsByDate[$day][] = $expenseRequest;
            }
        }

        $errors = [];
        $updates = [];   // item_id => ['payment' => x, 'label' => '...']
        $touched = [];   // item_id => csv line (duplicate-row guard)

        foreach ($rows as $row) {
            $line = $row['line'];
            $d = $row['data'];
            $rowErrors = [];
            $dateMatchesNothing = false;

            $refRaw = trim((string) ($d['ref'] ?? ''));
            $dateRaw = trim((string) ($d['date'] ?? ''));
            $expenseRequest = null;
            $candidateRequests = [];
            $dateKey = null;

            if ($dateRaw !== '') {
                $dateKey = $this->parsePaymentCsvDate($dateRaw);
                if (! $dateKey) {
                    $rowErrors[] = "Date '{$dateRaw}' is not a valid date (use YYYY-MM-DD or MM/DD/YYYY).";
                } else {
                    $candidateRequests = $requestsByDate[$dateKey] ?? [];
                    // Cyd 2026-11-14: the payment date on the sheet does not have
                    // to exist as a transaction date. Instead of rejecting the row,
                    // fall back to matching on Applicant / Agent / Particular /
                    // Currency alone when exactly one line in the agency fits.
                    if (count($candidateRequests) === 0) {
                        $dateMatchesNothing = true;
                    }
                }
            } elseif ($refRaw !== '') {
                // Legacy template (Ref# column) — still accepted.
                $candidateRequests = $requestsByRef[$norm($refRaw)] ?? [];
                if (count($candidateRequests) === 0) {
                    $rowErrors[] = "No expense request with Ref# '{$refRaw}' was found.";
                }
            } else {
                $rowErrors[] = 'Date is required.';
            }

            if (count($candidateRequests) === 1) {
                $expenseRequest = $candidateRequests[0];
            }

            // ---- Payment amount (required, >= 0) ----
            $paymentRaw = str_replace([',', '₱', '$', ' '], '', trim((string) ($d['payment'] ?? '')));
            $payment = null;
            if ($paymentRaw === '') {
                $rowErrors[] = 'Payment is required.';
            } elseif (! is_numeric($paymentRaw)) {
                $rowErrors[] = "Payment '{$paymentRaw}' must be a number.";
            } elseif ((float) $paymentRaw < 0) {
                $rowErrors[] = 'Payment cannot be negative.';
            } else {
                $payment = round((float) $paymentRaw, 2);
            }

            $currency = strtoupper(trim((string) ($d['currency'] ?? '')));
            $applicantRaw = trim((string) ($d['applicant'] ?? ''));
            $agentRaw = trim((string) ($d['agent'] ?? ''));
            $particularRaw = trim((string) ($d['particular'] ?? ''));

            $item = null;
            if (! $candidateRequests && $dateMatchesNothing) {
                // The sheet's date has no transaction. Match on the named columns
                // across the agency instead — the encoder is recording a payment
                // that landed on a different day than the transaction.
                //
                // This is filtered in SQL, not in PHP: Gulf has 23k+ lines, and
                // hydrating all of them per row blew the request up with a 500.
                $q = ExpenseRequestItem::query()
                    ->whereHas('expenseRequest', fn ($er) => $er->where('agency_id', $agencyId))
                    ->with(['expenseRequest', 'applicant', 'agent', 'account']);

                if ($agentRaw !== '') {
                    $q->whereHas('agent', fn ($a) => $a->whereRaw('LOWER(TRIM(name)) = ?', [$norm($agentRaw)]));
                }
                if ($particularRaw !== '') {
                    $q->whereRaw('LOWER(TRIM(COALESCE(particular, \'\'))) = ?', [$norm($particularRaw)]);
                }
                if ($currency !== '') {
                    $q->whereRaw('UPPER(TRIM(COALESCE(currency, \'\'))) = ?', [$currency]);
                }
                if ($applicantRaw !== '') {
                    $q->whereHas('applicant', function ($ap) use ($norm, $applicantRaw) {
                        $ap->whereRaw("LOWER(TRIM(CONCAT(COALESCE(first_name,''), ' ', COALESCE(middle_name,''), ' ', COALESCE(last_name,''), ' ', COALESCE(suffix,'')))) = ?", [$norm($applicantRaw)])
                           ->orWhereRaw("LOWER(TRIM(CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,'')))) = ?", [$norm($applicantRaw)])
                           ->orWhereRaw("LOWER(TRIM(CONCAT(COALESCE(last_name,''), ', ', COALESCE(first_name,'')))) = ?", [$norm($applicantRaw)]);
                    });
                }

                // Cap the work: enough to prove ambiguity, never the whole agency.
                $hits = $q->limit(50)->get();

                if ($hits->count() === 1) {
                    $item = $hits->first();
                    $expenseRequest = $item->expenseRequest;
                    $candidateRequests = [$expenseRequest];
                } elseif ($hits->count() === 0) {
                    $rowErrors[] = 'No transaction dated ' . Carbon::parse($dateKey)->format('M d, Y')
                        . ' was found, and no line anywhere in the agency matches the given Applicant / Agent / Particular / Currency.';
                } else {
                    $label = function ($it) {
                        $who = $it->applicant ? ($it->applicant->full_name ?? '') : 'no applicant';

                        return ($it->expenseRequest->reference_no ?? '#' . $it->id)
                            . ' · ' . ($it->expenseRequest->date ? Carbon::parse($it->expenseRequest->date)->format('M d, Y') : 'no date')
                            . ' · ' . $who
                            . ' · ' . ($it->agent->name ?? 'no agent')
                            . ' · ' . ($it->currency ?: 'PHP') . ' ' . number_format((float) $it->amount, 2);
                    };
                    $rowErrors[] = $hits->count() . ' line(s) elsewhere in the agency match the given columns, but the payment date is on none of them — add the Particular to pick one. Lines: '
                        . $hits->take(5)->map($label)->implode(' | ');
                }
            }

            // $item is already resolved when the date fallback above matched an
            // exact line; re-scoring that transaction's other lines could reject a
            // row we already proved correct.
            if ($candidateRequests && ! $item) {
                $candidates = collect($candidateRequests)
                    ->flatMap(fn ($er) => $er->items)
                    ->values();

                // Josh 2026-11-02: a hard AND-filter rejected a row the moment one
                // column differed (blank Applicant on office-charged lines, a
                // Particular typed slightly differently) and never said which lines
                // the date actually had. Every line is now scored on the columns the
                // sheet fills in, and the clear best score wins.
                $applicantMatches = function ($it) use ($norm, $applicantRaw) {
                    if ($applicantRaw === '' || ! $it->applicant) {
                        return false;
                    }
                    $lastFirst = ($it->applicant->last_name ?? '') . ', ' . ($it->applicant->first_name ?? '');

                    return $norm($it->applicant->full_name ?? '') === $norm($applicantRaw)
                        || $norm($lastFirst) === $norm($applicantRaw);
                };

                $scored = $candidates->map(function ($it) use (
                    $norm,
                    $agentRaw,
                    $particularRaw,
                    $currency,
                    $payment,
                    $applicantMatches
                ) {
                    $score = 0;
                    if ($applicantMatches($it)) {
                        $score += 100;
                    }
                    if ($agentRaw !== '' && $it->agent
                        && $norm($it->agent->name ?? '') === $norm($agentRaw)) {
                        $score += 50;
                    }
                    if ($particularRaw !== '' && $norm($it->particular ?? '') === $norm($particularRaw)) {
                        $score += 50;
                    }
                    if ($currency !== '' && strtoupper((string) $it->currency) === $currency) {
                        $score += 10;
                    }
                    if ($payment !== null && abs($payment - (float) $it->amount) < 0.01) {
                        $score += 25;
                    }

                    return ['item' => $it, 'score' => $score];
                });

                $bestScore = (int) $scored->max('score');
                $best = $scored->filter(fn ($s) => $s['score'] === $bestScore)->values();

                // A bare Currency hit (10) is not evidence — two PHP lines must never
                // be picked at random. One line on the date is always unambiguous.
                if ($candidates->count() === 1 || ($bestScore > 10 && $best->count() === 1)) {
                    $item = $best->first()['item'];
                    $expenseRequest = $item->expenseRequest ?? $expenseRequest;
                } else {
                    $given = array_values(array_filter([
                        $applicantRaw !== '' ? "Applicant '{$applicantRaw}'" : null,
                        $agentRaw !== '' ? "Agent '{$agentRaw}'" : null,
                        $particularRaw !== '' ? "Particular '{$particularRaw}'" : null,
                        $currency !== '' ? "Currency '{$currency}'" : null,
                    ]));
                    $givenText = $given ? ' (' . implode(' / ', $given) . ')' : '';

                    $label = function ($it) {
                        $who = $it->applicant
                            ? trim(($it->applicant->last_name ?? '') . ', ' . ($it->applicant->first_name ?? ''), ', ')
                            : 'no applicant';
                        $particular = trim((string) ($it->particular ?? ''));

                        return ($it->expenseRequest->reference_no ?? '#' . $it->id)
                            . ' · ' . $who
                            . ' · ' . ($it->agent->name ?? 'no agent')
                            . ' · ' . ($particular === '' ? 'no particular' : $particular)
                            . ' · ' . ($it->currency ?: 'PHP') . ' ' . number_format((float) $it->amount, 2);
                    };

                    $preview = $candidates->take(5)->map($label)->implode(' | ');
                    $more = $candidates->count() > 5 ? ' | … and ' . ($candidates->count() - 5) . ' more' : '';
                    $dateText = $dateKey
                        ? Carbon::parse($dateKey)->format('M d, Y')
                        : Carbon::parse($expenseRequest->date ?? now())->format('M d, Y');

                    if ($bestScore <= 10) {
                        // Tell the user *which* criteria failed instead of only
                        // dumping the date's lines (Josh 2026-11-08, "for fix").
                        // A wall of 20 rows does not show why the row missed.
                        $near = [];
                        if ($applicantRaw !== '') {
                            $hit = $candidates->first(fn ($it) => $it->applicant
                                && $norm(trim(($it->applicant->last_name ?? '') . ', ' . ($it->applicant->first_name ?? ''), ', ')) === $norm($applicantRaw));
                            if (! $hit) {
                                $near[] = "no line on {$dateText} has applicant '{$applicantRaw}'";
                            }
                        }
                        if ($agentRaw !== '') {
                            $hit = $candidates->first(fn ($it) => $it->agent && $norm($it->agent->name ?? '') === $norm($agentRaw));
                            if (! $hit) {
                                $near[] = "no line on {$dateText} has agent '{$agentRaw}'";
                            }
                        }
                        if ($particularRaw !== '') {
                            $hit = $candidates->first(fn ($it) => $norm($it->particular ?? '') === $norm($particularRaw));
                            if (! $hit) {
                                $near[] = "no line on {$dateText} has particular '{$particularRaw}'";
                            }
                        }
                        if ($payment !== null && ! $candidates->first(fn ($it) => abs($payment - (float) $it->amount) < 0.01)) {
                            $near[] = 'no line on ' . $dateText . ' is exactly ' . number_format($payment, 2);
                        }

                        $diagnosis = $near
                            ? ' Likely cause' . (count($near) === 1 ? '' : 's') . ': ' . implode('; ', $near) . '.'
                            : '';

                        $rowErrors[] = 'No line on the ' . $dateText . ' transaction matches' . $givenText . '.' . $diagnosis
                            . ' That date has ' . $candidates->count() . ' line(s): ' . $preview . $more;
                    } else {
                        $rowErrors[] = $best->count() . ' line(s) match' . $givenText
                            . ' equally — add the Applicant or Particular to pick one. Lines: ' . $preview . $more;
                    }
                }
            }

            if ($item && $this->ledgerBranchLocked()) {
                $agentBranch = $item->agent?->branch_id;
                if ($agentBranch !== null && (int) $agentBranch !== (int) auth()->user()->branch_id) {
                    $rowErrors[] = 'You can only record payments for agents in your own branch.';
                }
            }

            if ($item && $payment !== null) {
                // Cyd 2026-11-14: a payment larger than the line amount is allowed.
                // Encoders overpay lines (advances, corrected amounts) and the old
                // hard block forced them to fix the system instead of recording the
                // payment. The overpayment is surfaced as a warning, not an error.
                if (isset($touched[$item->id])) {
                    $rowErrors[] = 'Line ' . $touched[$item->id] . ' already sets a payment for this same line.';
                }
            }

            if ($rowErrors) {
                $errors[$line] = $rowErrors;
                continue;
            }

            $touched[$item->id] = $line;
            $updates[$item->id] = [
                'payment' => $payment,
                'label'   => ($expenseRequest->reference_no ?? '') . ' · ' . ($item->particular ?: ($item->account?->name ?? 'line')),
            ];
        }

        if (! empty($errors)) {
            return back()->withErrors(['csv_file' => 'No payments were saved — ' . count($errors) . ' row(s) need fixing.'])
                ->with('bulk_errors', $errors)
                ->withInput();
        }

        $changed = 0;
        DB::transaction(function () use ($updates, &$changed) {
            // Card 6b056e02 AC12: an uploaded payment must appear in the
            // request's Status / Transaction History. Grouped per request.
            $log = app(TransactionEditLog::class);
            $history = [];
            $actorName = auth()->user()->name ?? auth()->user()->username ?? 'user';

            foreach ($updates as $itemId => $payload) {
                $item = ExpenseRequestItem::whereKey($itemId)->first();
                if (! $item) {
                    continue;
                }
                $old = (float) $item->payment;
                $new = (float) $payload['payment'];

                if ((string) $item->payment !== (string) $payload['payment']) {
                    $changed++;
                }

                if (abs($old - $new) >= 0.005) {
                    $request = $item->expenseRequest;
                    if ($request) {
                        $history[$request->id] ??= ['model' => $request, 'segments' => []];
                        $lineItem = $item->particular ?: ($item->applicant?->last_name
                            ? trim(($item->applicant->last_name ?? '') . ', ' . ($item->applicant->first_name ?? ''), ', ')
                            : ($item->agent?->name ?: ('#' . $item->id)));

                        $history[$request->id]['segments'][] = 'Line "' . $lineItem . '" Payment: '
                            . number_format($old, 2) . ' -> ' . number_format($new, 2);
                    }
                }

                $item->update(['payment' => $payload['payment']]);
            }

            $log->writePaymentHistory($history, $actorName);
        });

        return redirect()->route('agent_report.index', ['tab' => 'payments'])
            ->with('success', 'Payment recorded on ' . count($updates) . ' transaction line(s).'
                . ($changed !== count($updates) ? ' (' . (count($updates) - $changed) . ' already had that amount.)' : ''));
    }

    /**
     * Admin + Accounting may record payments (same gate as deductions).
     */
    private function canManagePayments(): bool
    {
        return $this->canManageDeductions();
    }

    // ------------------------------------------------------------------
    // Tab 2b: Previous Payment — Gulf only (card 6b056b38)
    // ------------------------------------------------------------------

    /**
     * Bulk upload page for Previous Payments. Gulf only, at all four gates.
     */
    public function previousPaymentBulkUpload(): View
    {
        abort_unless(ModuleAccess::gulfOnly((int) auth()->user()->agency_id), 403);
        abort_unless($this->canManagePayments(), 403, 'Only Admin and Accounting can record payments.');

        return view('agent_report.previous_payment_bulk');
    }

    /**
     * CSV template for the previous-payment upload. The first line is exactly
     * `Date,Applicant,Agent,Currency,Payment,Particular` (accepted as-is).
     */
    public function previousPaymentBulkTemplate()
    {
        abort_unless(ModuleAccess::gulfOnly((int) auth()->user()->agency_id), 403);
        abort_unless($this->canManagePayments(), 403, 'Only Admin and Accounting can record payments.');

        $headers = ['Date', 'Applicant', 'Agent', 'Currency', 'Payment', 'Particular'];
        $sample = [
            [now()->toDateString(), 'Dela Cruz, Maria', 'Juan Dela Cruz', 'PHP', '1250.00', 'Commission before go-live'],
            [now()->toDateString(), '', 'Juan Dela Cruz', 'PHP', '0.00', ''],
        ];

        return response()->streamDownload(function () use ($headers, $sample) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($sample as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'previous-payments-template.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Import a previous-payment CSV. All-or-nothing: every row is validated
     * first; one bad row and nothing is inserted. Rows are INSERTED, never
     * matched to an expense line. Amount 0 is valid; negative is rejected.
     */
    public function previousPaymentBulkImport(Request $request): RedirectResponse
    {
        $agencyId = (int) auth()->user()->agency_id;
        abort_unless(ModuleAccess::gulfOnly($agencyId), 403);
        abort_unless($this->canManagePayments(), 403, 'Only Admin and Accounting can record payments.');

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $rows = $this->parsePaymentCsv($request->file('csv_file')->getRealPath());
        if (is_string($rows)) {
            return back()->withErrors(['csv_file' => $rows]);
        }

        $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));

        // Agents in this agency, indexed by normalised name. Exact-name match only.
        $agentsByName = [];
        foreach (Agent::where('agency_id', $agencyId)
                     ->when($this->ledgerBranchLocked(), fn ($q) => $this->branchLockedAgentCondition($q))
                     ->get() as $agent) {
            $agentsByName[$norm($agent->name)] = $agent;
        }

        // Applicants in this agency: "First Middle Last Suffix" and "Last, First".
        $applicantsByName = [];
        foreach (Applicant::where('agency_id', $agencyId)->get() as $applicant) {
            $applicantsByName[$norm($applicant->full_name)][] = $applicant;
            $lastFirst = trim(($applicant->last_name ?? '') . ', ' . ($applicant->first_name ?? ''), ', ');
            if ($lastFirst !== '') {
                $applicantsByName[$norm($lastFirst)][] = $applicant;
            }
        }

        $errors = [];
        $inserts = [];

        foreach ($rows as $row) {
            $line = $row['line'];
            $d = $row['data'];
            $rowErrors = [];

            // ---- Date (required) ----
            $dateRaw = trim((string) ($d['date'] ?? ''));
            $date = null;
            if ($dateRaw === '') {
                $rowErrors[] = 'Date is required.';
            } else {
                $date = $this->parsePaymentCsvDate($dateRaw);
                if (! $date) {
                    $rowErrors[] = "Date '{$dateRaw}' is not a valid date (use YYYY-MM-DD or MM/DD/YYYY).";
                }
            }

            // ---- Amount: required, >= 0 (0 is valid; negative is not) ----
            $paymentRaw = str_replace([',', '₱', '$', ' '], '', trim((string) ($d['payment'] ?? '')));
            $amount = null;
            if ($paymentRaw === '') {
                $rowErrors[] = 'Payment is required (use 0 if the amount is zero).';
            } elseif (! is_numeric($paymentRaw)) {
                $rowErrors[] = "Payment '{$paymentRaw}' must be a number.";
            } elseif ((float) $paymentRaw < 0) {
                $rowErrors[] = "Payment '{$paymentRaw}' cannot be negative.";
            } else {
                $amount = round((float) $paymentRaw, 2);
            }

            // ---- Currency: PHP or USD, default PHP ----
            $currency = strtoupper(trim((string) ($d['currency'] ?? '')));
            if ($currency === '') {
                $currency = 'PHP';
            } elseif (! in_array($currency, ['PHP', 'USD'], true)) {
                $rowErrors[] = "Currency '{$currency}' must be PHP or USD.";
            }

            // ---- Agent: exact name in this agency; blank is allowed ----
            $agentRaw = trim((string) ($d['agent'] ?? ''));
            $agentId = null;
            if ($agentRaw !== '') {
                if (isset($agentsByName[$norm($agentRaw)])) {
                    $agentId = $agentsByName[$norm($agentRaw)]->id;
                } else {
                    $rowErrors[] = "Agent '{$agentRaw}' was not found in this agency.";
                }
            }

            // ---- Applicant: existing applicant; blank is allowed ----
            $applicantRaw = trim((string) ($d['applicant'] ?? ''));
            $applicantId = null;
            if ($applicantRaw !== '') {
                $hits = $applicantsByName[$norm($applicantRaw)] ?? [];
                $hits = collect($hits)->unique('id')->values();
                if ($hits->isEmpty()) {
                    $rowErrors[] = "Applicant '{$applicantRaw}' was not found in this agency.";
                } elseif ($hits->count() > 1) {
                    $rowErrors[] = "Applicant '{$applicantRaw}' matches {$hits->count()} records — the name is ambiguous.";
                } else {
                    $applicantId = $hits->first()->id;
                }
            }

            // ---- Particular: optional, max 1000 ----
            $particularRaw = trim((string) ($d['particular'] ?? ''));
            if (mb_strlen($particularRaw) > 1000) {
                $rowErrors[] = 'Particular is longer than 1000 characters.';
            }

            if ($rowErrors) {
                $errors[$line] = $rowErrors;
                continue;
            }

            $inserts[] = [
                'agency_id'    => $agencyId,
                'user_id'      => auth()->id(),
                'agent_id'     => $agentId,
                'applicant_id' => $applicantId,
                'date'         => $date,
                'currency'     => $currency,
                'amount'       => $amount,
                'particular'   => $particularRaw === '' ? null : $particularRaw,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        if (! empty($errors)) {
            return back()->withErrors(['csv_file' => 'No previous payments were saved — ' . count($errors) . ' row(s) need fixing.'])
                ->with('bulk_errors', $errors)
                ->withInput();
        }

        if (empty($inserts)) {
            return redirect()->route('agent_report.index', ['tab' => 'previous-payments'])
                ->with('error', 'The file had no data rows to upload.');
        }

        DB::transaction(function () use ($inserts) {
            foreach (array_chunk($inserts, 200) as $chunk) {
                AgentPreviousPayment::insert($chunk);
            }
        });

        return redirect()->route('agent_report.index', ['tab' => 'previous-payments'])
            ->with('success', count($inserts) . ' previous payment row(s) added.');
    }

    /**
     * Admin-only delete with a reason. These rows exist nowhere else, so the
     * row is removed outright (the delete reason is written to the audit trail
     * by AuditsChanges).
     */
    public function previousPaymentDestroy(Request $request, AgentPreviousPayment $previousPayment): RedirectResponse
    {
        $this->authorizeAgency((int) $previousPayment->agency_id);
        abort_unless(ModuleAccess::gulfOnly((int) $previousPayment->agency_id), 403);
        $this->authorizeTransactionDelete();
        $this->authorizeAgentBranch($previousPayment->agent);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($previousPayment, $validated) {
            $previousPayment->forceFill([
                'deleted_by'    => auth()->id(),
                'delete_reason' => $validated['reason'],
            ]);
            $previousPayment->delete();
        });

        return redirect()->route('agent_report.index', ['tab' => 'previous-payments'])
            ->with('success', 'Previous payment deleted.');
    }

    /**
     * Normalise the Date cell of the payment CSV to Y-m-d (null when invalid).
     * Accepts Excel serials, ISO, MM/DD/YYYY, DD-MM-YYYY and "M d, Y".
     */
    private function parsePaymentCsvDate(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        // Excel serial date = days since 1899-12-30.
        if (preg_match('/^\d{5}(\.\d+)?$/', $raw)) {
            return Carbon::create(1899, 12, 30)->addDays((int) $raw)->toDateString();
        }

        foreach (['Y-m-d', 'm/d/Y', 'n/j/Y', 'd/m/Y', 'm-d-Y', 'd-M-Y', 'M d, Y', 'M j, Y', 'F j, Y'] as $format) {
            $parsed = \DateTime::createFromFormat('!'.$format, $raw);
            if ($parsed && $parsed->format($format) === $raw) {
                return $parsed->format('Y-m-d');
            }
        }

        try {
            return Carbon::parse($raw)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse the payment CSV into ['line' => n, 'data' => [...]] rows.
     */
    private function parsePaymentCsv(string $path)
    {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return 'Could not read the uploaded file.';
        }

        if (str_starts_with($raw, "\xFF\xFE")) {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16LE');
        } elseif (str_starts_with($raw, "\xFE\xFF")) {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16BE');
        } elseif (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }
        if ($raw !== '' && ! mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');
        }
        if (trim($raw) === '') {
            return 'The file is empty.';
        }

        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        $aliases = [
            'date'                => 'date',
            'payment date'        => 'date',
            'transaction date'    => 'date',
            'date paid'           => 'date',
            'ref'                 => 'ref',
            'ref#'                => 'ref',
            'ref #'               => 'ref',
            'ref no'              => 'ref',
            'ref. no'             => 'ref',
            'refno'               => 'ref',
            'reference'           => 'ref',
            'reference no'        => 'ref',
            'reference number'    => 'ref',
            'ref#/ar#'            => 'ref',
            'applicant'           => 'applicant',
            'applicant name'      => 'applicant',
            'agent'               => 'agent',
            'agent name'          => 'agent',
            'currency'            => 'currency',
            'payment'             => 'payment',
            'payment amount'      => 'payment',
            'amount'              => 'payment',
            'amount paid'         => 'payment',
            'particular'          => 'particular',
            'particular/description' => 'particular',
            'description'         => 'particular',
        ];

        $parse = function (string $delimiter) use ($raw, $aliases) {
            $handle = fopen('php://temp', 'r+');
            fwrite($handle, $raw);
            rewind($handle);

            $parsed = [];
            while (($cells = fgetcsv($handle, 0, $delimiter)) !== false) {
                $parsed[] = $cells;
            }
            fclose($handle);

            if (empty($parsed)) {
                return 'The file has no readable rows.';
            }

            $headerIndex = null;
            $fieldByIndex = [];
            foreach (array_slice($parsed, 0, 30, true) as $i => $cells) {
                if (empty(array_filter(array_map('trim', $cells), fn ($c) => $c !== ''))) {
                    continue;
                }
                $candidate = [];
                foreach ($cells as $idx => $header) {
                    $key = $this->normaliseHeaderKey((string) $header);
                    $field = $aliases[$key] ?? null;
                    if ($field && ! in_array($field, $candidate, true)) {
                        $candidate[$idx] = $field;
                    }
                }
                if ((in_array('date', $candidate, true) || in_array('ref', $candidate, true)) && in_array('payment', $candidate, true)) {
                    $headerIndex = $i;
                    $fieldByIndex = $candidate;
                    break;
                }
            }

            if ($headerIndex === null) {
                return 'No header row found. The first row must name the columns: Date, Applicant, Agent, Currency, Payment, Particular.';
            }

            $rows = [];
            foreach (array_slice($parsed, $headerIndex + 1, null, true) as $i => $cells) {
                $lineNo = $i + 1;
                if (empty(array_filter(array_map('trim', $cells), fn ($c) => $c !== ''))) {
                    continue;
                }
                $data = [];
                foreach ($fieldByIndex as $idx => $field) {
                    $data[$field] = $cells[$idx] ?? '';
                }
                $rows[] = ['line' => $lineNo, 'data' => $data];
            }

            if (empty($rows)) {
                return 'The file has no data rows — add at least one payment below the header.';
            }

            if (count($rows) > 2000) {
                return 'The file has too many rows (' . count($rows) . '). Split it into batches of 2000 or fewer.';
            }

            return $rows;
        };

        $lastError = null;
        foreach ([',', ';', "\t"] as $delimiter) {
            $result = $parse($delimiter);
            if (is_array($result)) {
                return $result;
            }
            $lastError = $result;
        }

        return $lastError ?? 'Could not read the file.';
    }

    /**
     * Review — admin-only status/history behavior mirrors Receivable review,
     * but AgentDeduction has no status field: this is a read-only review page.
     */
    /**
     * Cyd 2026-11-01 (card 124) — "Admin: able to delete transaction on both
     * Deduction and Payment tabs."
     *
     * Deletes never destroy the row outright: the deduction is soft-deleted and
     * stamped with the admin + reason, and a payment delete only zeroes the
     * payment amount on the expense item (the expense transaction itself stays
     * intact for the ledger). Both routes are Admin-only, mirroring the
     * Expenses module delete gate.
     */
    public function deductionDestroy(Request $request, AgentDeduction $agentDeduction): RedirectResponse
    {
        $this->authorizeAgency((int) $agentDeduction->agency_id);
        $this->authorizeTransactionDelete();
        $this->authorizeAgentBranch($agentDeduction->agent);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($agentDeduction, $validated) {
            $agentDeduction->forceFill([
                'deleted_by'    => auth()->id(),
                'delete_reason' => $validated['reason'],
            ])->save();

            $agentDeduction->delete();
        });

        return redirect()->route('agent_report.index', ['tab' => 'deductions'])
            ->with('success', 'Deduction transaction deleted.');
    }

    /**
     * Payments tab delete: the listed row is the payment amount recorded on an
     * expense item, so deleting it clears the payment (back to 0) and writes an
     * audit note on the expense — the expense request and its amount stay put.
     */
    public function paymentDestroy(Request $request, ExpenseRequestItem $expenseRequestItem): RedirectResponse
    {
        $expenseRequest = $expenseRequestItem->expenseRequest;
        if (! $expenseRequest) {
            abort(404);
        }
        $this->authorizeAgency((int) $expenseRequest->agency_id);
        $this->authorizeTransactionDelete();
        $this->authorizeAgentBranch($expenseRequestItem->agent);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $payment = (float) $expenseRequestItem->payment;
        if ($payment <= 0) {
            return redirect()->route('agent_report.index', ['tab' => 'payments'])
                ->with('error', 'That transaction has no payment to delete.');
        }

        DB::transaction(function () use ($expenseRequestItem, $expenseRequest, $payment, $validated) {
            $expenseRequestItem->forceFill(['payment' => 0])->save();

            ExpenseRequestStatusHistory::create([
                'expense_request_id' => $expenseRequest->id,
                'agency_id'          => $expenseRequest->agency_id,
                'user_id'            => auth()->id(),
                'from_status'        => $expenseRequest->status,
                'to_status'          => $expenseRequest->status,
                'note'               => 'Payment of ' . number_format($payment, 2) . ' deleted by '
                    . (auth()->user()->name ?? auth()->user()->username)
                    . ': ' . $validated['reason'],
            ]);
        });

        return redirect()->route('agent_report.index', ['tab' => 'payments'])
            ->with('success', 'Payment transaction deleted.');
    }

    /** Only Admin (super_admin/admin) may delete Deduction / Payment transactions. */
    private function authorizeTransactionDelete(): void
    {
        if (! in_array((string) auth()->user()->user_type, ['super_admin', 'admin'], true)) {
            abort(403, 'Only Admin can delete transactions.');
        }
    }

    public function deductionShow(AgentDeduction $agentDeduction): View
    {
        $this->authorizeAgency($agentDeduction->agency_id);
        $this->authorizeAgentBranch($agentDeduction->agent);

        return view('agent_report.deduction_show', [
            'deduction' => $agentDeduction->load(['agent', 'applicant', 'encoder']),
            'canEdit'   => $this->canManageDeductions(),
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

    // ------------------------------------------------------------------
    // Tab 4: Starting Balance — bulk upload (card 6af174a1, Cyd 2026-11-09)
    // Header per the card: Date, Account, Agent, Amount, Particular
    // ------------------------------------------------------------------

    public function startingBalanceBulkUpload(): View
    {
        abort_unless($this->canManageDeductions(), 403, 'Only Admin and Accounting can create starting balances.');

        return view('agent_report.starting_balance_bulk');
    }

    /**
     * CSV template for the starting-balance bulk upload. The card fixes the
     * header as: Date, Account, Agent, Amount, Particular.
     */
    public function startingBalanceBulkTemplate()
    {
        $headers = ['Date', 'Account', agent_header('Agent'), 'Amount', 'Particular'];
        $today = now()->toDateString();
        $sample = [
            [$today, StartingBalance::ACCOUNT, 'Juan Dela Cruz', '5000.00', 'Opening balance carried forward'],
            [$today, StartingBalance::ACCOUNT, 'Maria Santos', '2500.00', ''],
        ];

        return response()->streamDownload(function () use ($headers, $sample) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($sample as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'starting-balances-template.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    public function startingBalanceBulkImport(Request $request): RedirectResponse
    {
        abort_unless($this->canManageDeductions(), 403, 'Only Admin and Accounting can create starting balances.');

        $agencyId = auth()->user()->agency_id;

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $rows = $this->parseStartingBalanceCsv($request->file('csv_file')->getRealPath());
        if (is_string($rows)) {
            return back()->withErrors(['csv_file' => $rows]);
        }

        $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));

        $agentByName = [];
        foreach ($this->scopeAgentsByBranch(Agent::where('agency_id', $agencyId))->get(['id', 'branch_id', 'name']) as $agent) {
            $agentByName[$norm($agent->name)][] = $agent;
        }

        // One starting balance per agent (same rule as the single-entry form).
        $already = StartingBalance::where('agency_id', $agencyId)->pluck('agent_id')->flip();

        $errors = [];
        $validatedRows = [];
        $seenAgentIds = [];

        foreach ($rows as $row) {
            $d = $row['data'];
            $line = $row['line'];
            $rowErrors = [];

            // ---- Date (blank = today) ----
            $dateRaw = trim((string) ($d['date'] ?? ''));
            $date = null;
            if ($dateRaw === '') {
                $date = now()->toDateString();
            } elseif (($ts = strtotime($dateRaw)) !== false) {
                $date = date('Y-m-d', $ts);
            } else {
                $rowErrors[] = "Date '{$dateRaw}' is not a valid date.";
            }

            // ---- Account (optional; fixed to Starting Balance) ----
            $accountRaw = trim((string) ($d['account'] ?? ''));
            if ($accountRaw !== '' && $norm($accountRaw) !== $norm(StartingBalance::ACCOUNT)) {
                $rowErrors[] = "Account '{$accountRaw}' is not allowed here — this upload only creates "
                    . StartingBalance::ACCOUNT . ' entries. Leave the column blank or type "'
                    . StartingBalance::ACCOUNT . '".';
            }

            // ---- Agent (required, must exist in this agency/branch) ----
            $agentRaw = trim((string) ($d['agent'] ?? ''));
            $agent = null;
            if ($agentRaw === '') {
                $rowErrors[] = 'Agent is required.';
            } else {
                $matches = $agentByName[$norm($agentRaw)] ?? [];
                if (count($matches) === 0) {
                    $rowErrors[] = "Agent '{$agentRaw}' was not found in your agency.";
                } elseif (count($matches) > 1) {
                    $rowErrors[] = "Agent '{$agentRaw}' matches more than one agent — make the name unique first.";
                } else {
                    $agent = $matches[0];
                }
            }

            // ---- Amount (required, > 0) ----
            $amountRaw = trim((string) ($d['amount'] ?? ''));
            $amountRaw = str_replace([',', '₱', ' '], '', $amountRaw);
            $amount = null;
            if ($amountRaw === '') {
                $rowErrors[] = 'Amount is required.';
            } elseif (! is_numeric($amountRaw)) {
                $rowErrors[] = "Amount '{$amountRaw}' must be a number.";
            } elseif ((float) $amountRaw <= 0) {
                $rowErrors[] = 'Amount must be greater than zero.';
            } else {
                $amount = round((float) $amountRaw, 2);
            }

            $particular = trim((string) ($d['particular'] ?? '')) ?: null;

            if ($agent) {
                // Duplicate against the database and within the file — a second
                // starting balance for the same agent is never valid.
                if ($already->has($agent->id)) {
                    $rowErrors[] = "'{$agent->name}' already has a starting balance. Delete the existing one first, or edit it.";
                } elseif (isset($seenAgentIds[$agent->id])) {
                    $rowErrors[] = "'{$agent->name}' appears more than once in this file (also on line {$seenAgentIds[$agent->id]}).";
                }
                $seenAgentIds[$agent->id] ??= $line;
            }

            if ($rowErrors) {
                $errors[$line] = $rowErrors;
                continue;
            }

            $validatedRows[] = [
                'agency_id'    => $agencyId,
                'user_id'      => auth()->id(),
                'agent_id'     => $agent->id,
                'applicant_id' => null,
                'date'         => $date,
                'account'      => StartingBalance::ACCOUNT,
                'amount'       => $amount,
                'particular'   => $particular,
            ];
        }

        if (! empty($errors)) {
            return back()->withErrors(['csv_file' => 'No rows were imported — ' . count($errors) . ' row(s) need fixing.'])
                ->with('bulk_errors', $errors)
                ->withInput();
        }

        DB::transaction(function () use ($validatedRows) {
            foreach ($validatedRows as $row) {
                StartingBalance::create($row);
            }
        });

        return redirect()->route('agent_report.index', ['tab' => 'starting-balances'])
            ->with('success', 'Imported ' . count($validatedRows) . ' starting balance(s).');
    }

    /**
     * Parse the starting-balance CSV into [{line, data}]. Accepts comma /
     * semicolon / tab, BOM and UTF-16 (Excel), and any column order — columns
     * are matched by name, with the card's header as the canonical set:
     * Date, Account, Agent, Amount, Particular.
     */
    private function parseStartingBalanceCsv(string $path)
    {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return 'Could not read the uploaded file.';
        }

        if (str_starts_with($raw, "\xFF\xFE")) {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16LE');
        } elseif (str_starts_with($raw, "\xFE\xFF")) {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16BE');
        } elseif (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }
        if ($raw !== '' && ! mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');
        }
        if (trim($raw) === '') {
            return 'The file is empty.';
        }

        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        $aliases = [
            'date'            => 'date',
            'transaction date' => 'date',
            'account'         => 'account',
            'agent'           => 'agent',
            'agent name'      => 'agent',
            'amount'          => 'amount',
            'particular'      => 'particular',
            'particular/description' => 'particular',
            'particulars'     => 'particular',
            'description'     => 'particular',
            'remarks'         => 'particular',
            'remarks/description' => 'particular',
            'note'            => 'particular',
            'notes'           => 'particular',
        ];

        $parse = function (string $delimiter) use ($raw, $aliases) {
            $handle = fopen('php://temp', 'r+');
            fwrite($handle, $raw);
            rewind($handle);

            $parsed = [];
            while (($cells = fgetcsv($handle, 0, $delimiter)) !== false) {
                $parsed[] = $cells;
            }
            fclose($handle);

            if (empty($parsed)) {
                return 'The file has no readable rows.';
            }

            $headerIndex = null;
            $fieldByIndex = [];
            $unmapped = [];

            foreach (array_slice($parsed, 0, 30, true) as $i => $cells) {
                if (empty(array_filter(array_map('trim', $cells), fn ($c) => $c !== ''))) {
                    continue;
                }
                $candidate = [];
                foreach ($cells as $idx => $header) {
                    $key = $this->normaliseHeaderKey((string) $header);
                    $field = $aliases[$key] ?? null;
                    if ($field && ! in_array($field, $candidate, true)) {
                        $candidate[$idx] = $field;
                    }
                }
                if (in_array('agent', $candidate, true) && in_array('amount', $candidate, true)) {
                    $headerIndex = $i;
                    $fieldByIndex = $candidate;
                    // A description-looking column we do not map would silently
                    // drop the text — refuse instead (same lesson as the
                    // deductions upload, card 6af170f5).
                    foreach ($cells as $idx => $header) {
                        if (isset($candidate[$idx])) {
                            continue;
                        }
                        $key = $this->normaliseHeaderKey((string) $header);
                        if ($key !== '' && preg_match('/particular|descri|remark|note|reason|narrat|deta?il|purpose|comment|memo/', $key)) {
                            $unmapped[] = trim((string) $header);
                        }
                    }
                    break;
                }
            }

            if (! empty($unmapped)) {
                return 'The file has a column this uploader does not recognise: '
                    . implode(', ', array_unique($unmapped))
                    . '. Rename it to Particular so the text is not lost.';
            }

            if ($headerIndex === null) {
                return 'No header row found. The first row must name the columns: Date, Account, Agent, Amount, Particular.';
            }

            $rows = [];
            foreach (array_slice($parsed, $headerIndex + 1, null, true) as $i => $cells) {
                $lineNo = $i + 1;
                if (empty(array_filter(array_map('trim', $cells), fn ($c) => $c !== ''))) {
                    continue;
                }
                $data = [];
                foreach ($fieldByIndex as $idx => $field) {
                    $data[$field] = $cells[$idx] ?? '';
                }
                $rows[] = ['line' => $lineNo, 'data' => $data];
            }

            if (empty($rows)) {
                return 'The file has no data rows — add at least one starting balance below the header.';
            }

            if (count($rows) > 2000) {
                return 'The file has too many rows (' . count($rows) . '). Split it into batches of 2000 or fewer.';
            }

            return $rows;
        };

        $lastError = null;
        foreach ([',', ';', "\t"] as $delimiter) {
            $result = $parse($delimiter);
            if (is_array($result)) {
                return $result;
            }
            if (str_contains((string) $result, 'does not recognise')) {
                return $result;
            }
            $lastError = $result;
        }

        return $lastError ?? 'Could not read the file.';
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
        $this->authorizeAgentFilter($agentId);

        $agents = $this->scopeAgentsByBranch(Agent::where('agency_id', $agencyId))->with('branch')->orderBy('name')->get();
        $converter = new CurrencyConverter();

        $rows = $this->buildReportRows($agents, $converter, $agentId);

        return view('agent_report.print', [
            'agents'    => $agents,
            'agentId'   => $agentId,
            'reportRows' => $rows,
            'totals'    => [
                'commission'     => $rows->sum('commission'),
                'cash_advance'   => $rows->sum('cash_advance'),
                'backout_repat'  => $rows->sum('backout_repat'),
                'receivable'     => $rows->sum('receivable'),
                // print.blade.php renders this column; leaving it out of the
                // array 500'd the whole route (card 6b02fb4e).
                'return_payment' => $rows->sum('return_payment'),
                'payments'       => $rows->sum('payments'),
                'balance'        => $rows->sum('balance'),
            ],
            'printedAt' => now(),
        ]);
    }

    public function export(Request $request)
    {
        $agencyId = auth()->user()->agency_id;
        $agentId  = $request->input('agent_id');
        $this->authorizeAgentFilter($agentId);

        $agents = $this->scopeAgentsByBranch(Agent::where('agency_id', $agencyId))->with('branch')->orderBy('name')->get();
        $converter = new CurrencyConverter();

        $rows = $this->buildReportRows($agents, $converter, $agentId);

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, [
            agent_header('Agent'), 'Branch', 'Total Commission', 'Total Cash Advance',
            'Total Backout and Repat', 'Total Receivable (AR)',
            // Return Payment sits between Receivable and Payments on screen
            // and in the print view; the CSV skipped it, so a downloaded
            // ledger's columns did not line up with either.
            'Total Return Payment',
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
                number_format($row['return_payment'], 2),
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
     *  - payments           : AgentDeduction Paid rows + payment amounts
     *    recorded on expense transactions (card 130)
     *  - balance            = cashAdvance + backoutRepat - receivables + returnPayment - payments
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

        $releasedCommission = $items->whereIn('account_id', $commissionAccountIds)
            ->reject(fn ($i) => $returnAccountIds->contains($i->account_id))
            ->values();
        $cashAdvance        = $items->whereIn('account_id', $cashAdvanceAccountIds)->values();

        // Return Payment (Cyd 2026-11-04): released items on the "Return"
        // account — money handed back to the agent. Own table on the ledger
        // and added to the balance.
        $returnPayments = $items->whereIn('account_id', $returnAccountIds)->values();
        $totalReturnPayment = $returnPayments
            ->sum(fn ($i) => $converter->toPhp((float) $i->amount, $i->currency));

        // Total Commission = all commission accounts MINUS Return, NET of the
        // payment already handed to the agent (Cyd 2026-11-04, card 131).
        $totalCommission = $releasedCommission
            ->sum(fn ($i) => $converter->toPhp($i->netAmount(), $i->currency));
        $totalCashAdvance = $cashAdvance->sum(fn ($i) => $converter->toPhp((float) $i->amount, $i->currency));

        // Backout & Repat (Deduction) and Payment (Paid) from the Deduction & Paid module.
        $deductions = AgentDeduction::query()
            ->where('agent_id', $agent->id)
            ->with(['agent', 'applicant'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
        $backoutRepat = $deductions->where('account', AgentDeduction::ACCOUNT_DEDUCTION)->values();
        $paidEntries  = $deductions->where('account', AgentDeduction::ACCOUNT_PAID);

        // Payments (Cyd 2026-11-04, card 130): two complementary sources so a
        // payment recorded on an expense transaction actually shows up here.
        //  1. AgentDeduction "Paid" rows — approval-generated net amount
        //     (amount − payment) plus manual/bulk Paid entries.
        //  2. the Payment amount recorded on Expense & Payment lines.
        // No double count: an approval row already nets out the payment.
        $expensePayments = ExpenseRequestItem::query()
            ->where('agent_id', $agent->id)
            ->where('payment', '>', 0)
            ->whereHas('expenseRequest', fn ($er) => $er->where('agency_id', $agencyId))
            ->with(['expenseRequest', 'applicant', 'account'])
            ->orderByDesc('id')
            ->get();

        $payments = $paidEntries
            ->map(fn (AgentDeduction $d) => [
                'date'       => $d->date,
                'account'    => $d->account,
                'agent'      => $d->agent?->name ?? $agent->name,
                'applicant'  => $d->applicant?->full_name,
                'amount'     => (float) $d->amount,
                'particular' => $d->particular,
                'source'     => 'paid_entry',
            ])
            ->concat($expensePayments->map(fn (ExpenseRequestItem $i) => [
                'date'       => $i->expenseRequest?->date,
                'account'    => $i->account?->name ?? '—',
                'agent'      => $agent->name,
                'applicant'  => $i->applicant?->full_name,
                'amount'     => $converter->toPhp((float) $i->payment, $i->currency),
                'particular' => 'Expense #' . ($i->expenseRequest?->reference_no ?? '—')
                    . ($i->particular ? ': ' . $i->particular : ''),
                'source'     => 'expense_payment',
            ]));

        // Previous Payment (Gulf only, card 6b056b38): pre-go-live history rows,
        // listed in the same Payment table and counted in Total Payment, so this
        // page matches the Agents table (buildReportRows()).
        if (ModuleAccess::gulfOnly($agencyId)) {
            $payments = $payments->concat(
                AgentPreviousPayment::query()
                    ->where('agency_id', $agencyId)
                    ->where('agent_id', $agent->id)
                    ->with(['applicant'])
                    ->get()
                    ->map(fn (AgentPreviousPayment $p) => [
                        'date'       => $p->date,
                        'account'    => 'Previous Payment',
                        'agent'      => $agent->name,
                        'applicant'  => $p->applicant?->full_name,
                        'amount'     => $converter->toPhp((float) $p->amount, $p->currency),
                        'particular' => $p->particular,
                        'source'     => 'previous_payment',
                    ])
            );
        }

        $payments = $payments
            ->sortByDesc(fn (array $p) => $p['date']?->timestamp ?? 0)
            ->values();

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
            'returnPayments'     => $returnPayments,
            'payments'           => $payments,
            'totalCommission'    => $totalCommission,
            'totalCashAdvance'   => $totalCashAdvance,
            'totalBackoutRepat'  => $totalBackoutRepat,
            'totalReceivables'   => $totalReceivables,
            'totalReturnPayment' => $totalReturnPayment,
            'totalPayment'       => $totalPayment,
            'balance'            => $totalCashAdvance + $totalBackoutRepat - $totalReceivables + $totalReturnPayment - $totalPayment,
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
     *  - payments      : AgentDeduction Paid rows + payment amounts recorded
     *                    on expense transactions (card 130)
     *  - balance       = cash_advance + backout_repat − receivable + return_payment − payments
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
            ->get(['id', 'agent_id', 'account_id', 'currency', 'amount', 'payment']);

        $commission = [];
        $cashAdvance = [];
        $returnPayment = [];
        foreach ($items as $item) {
            $agentKey = (int) $item->agent_id;
            $amount = $converter->toPhp((float) $item->amount, $item->currency);

            if ($cashAdvanceAccountIds->contains($item->account_id)) {
                $cashAdvance[$agentKey] = ($cashAdvance[$agentKey] ?? 0.0) + $amount;
            } elseif ($returnAccountIds->contains($item->account_id)) {
                // Return Payment (Cyd 2026-11-04): money returned to the agent.
                $returnPayment[$agentKey] = ($returnPayment[$agentKey] ?? 0.0) + $amount;
            } elseif ($commissionAccountIds->contains($item->account_id)) {
                // Net of payment, so the summary agrees with the agent ledger.
                $commission[$agentKey] = ($commission[$agentKey] ?? 0.0)
                    + $converter->toPhp($item->netAmount(), $item->currency);
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

        // Expense & Payment lines (Cyd 2026-11-04, card 130): a payment recorded
        // on a transaction counts as a payment on the ledger too. Complementary
        // to the approval-generated Paid rows (those already net out payment).
        $expensePayments = $this->scopeQueryAgentsByBranch(ExpenseRequestItem::query())
            ->whereNotNull('agent_id')
            ->where('payment', '>', 0)
            ->whereHas('expenseRequest', fn ($er) => $er->where('agency_id', $agencyId))
            ->get(['agent_id', 'currency', 'payment']);

        foreach ($expensePayments as $p) {
            $key = (int) $p->agent_id;
            $paidByAgent[$key] = ($paidByAgent[$key] ?? 0.0)
                + $converter->toPhp((float) $p->payment, $p->currency);
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

        // Previous Payment (Gulf only, card 6b056b38): history rows are money
        // already paid to the agent, so they add to Payments — and therefore
        // reduce the Balance — exactly like a recorded payment on a transaction.
        $previousPaymentsByAgent = [];
        if (ModuleAccess::gulfOnly($agencyId)) {
            $prevAgg = $this->scopeQueryAgentsByBranch(AgentPreviousPayment::query())
                ->where('agency_id', $agencyId)
                ->whereNotNull('agent_id')
                ->select('agent_id', 'currency')
                ->selectRaw('COALESCE(SUM(amount),0) as total')
                ->groupBy('agent_id', 'currency')
                ->get();

            foreach ($prevAgg as $p) {
                $key = (int) $p->agent_id;
                $previousPaymentsByAgent[$key] = ($previousPaymentsByAgent[$key] ?? 0.0)
                    + $converter->toPhp((float) $p->total, (string) $p->currency);
            }
        }

        return $agents
            ->filter(fn (Agent $a) => $agentId === null || (int) $a->id === (int) $agentId)
            ->map(function (Agent $agent) use ($commission, $cashAdvance, $returnPayment, $backoutByAgent, $paidByAgent, $recAgg, $previousPaymentsByAgent) {
                $key = (int) $agent->id;
                $comm    = (float) ($commission[$key] ?? 0);
                $ca      = (float) ($cashAdvance[$key] ?? 0);
                $ret     = (float) ($returnPayment[$key] ?? 0);
                $backout = (float) ($backoutByAgent[$key] ?? 0);
                $paid    = (float) ($paidByAgent[$key] ?? 0) + (float) ($previousPaymentsByAgent[$key] ?? 0);
                $recv    = (float) ($recAgg->get($agent->id)->total ?? 0);

                return [
                    'id'             => $agent->id,
                    'agent'          => $agent->name,
                    'branch'         => $agent->branch?->name ?? '-',
                    'commission'     => $comm,
                    'cash_advance'   => $ca,
                    'backout_repat'  => $backout,
                    'receivable'     => $recv,
                    'return_payment' => $ret,
                    'payments'       => $paid,
                    'balance'        => $ca + $backout - $recv + $ret - $paid,
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

        // isBranchLocked() deliberately returns false for head-office accounts,
        // so a Main Office user is not "branch locked" by the app-wide rule.
        // isAgentLedgerMainOfficeOnly() names the two head-office accounts that
        // must still be scoped to their own (head-office) agent pool in this
        // module. Both cases reduce to "only my own branch's agents" below.
        return $user && ($user->isAgentLedgerMainOfficeOnly()
            || ($user->isBranchLocked() && ! $user->isMainOffice()));
    }

    /**
     * (Branch feature) Branch-locked users may only work with agents of their
     * own branch.
     *
     * (Cyd 2026-10-23, "Agents Report Module") Branch = *only* the agents
     * registered under their branch — unassigned (branch_id null) agents are
     * no longer pulled in here. Main Office accounts stay unlocked and see
     * every agent.
     */
    private function branchLockedAgentCondition($query): void
    {
        $user = auth()->user();
        $query->where('branch_id', $user->branch_id);
    }

    /** Applicants follow the Agent branch scope (branch users only). */
    private function scopeApplicantsByBranch($query)
    {
        return $query->when($this->ledgerBranchLocked(), fn ($q) => $q->whereHas('agent', fn ($a) => $this->branchLockedAgentCondition($a)));
    }

    private function scopeAgentsByBranch($query)
    {
        // Branch accounts: only the agents registered under their branch.
        return $query->when($this->ledgerBranchLocked(), fn ($q) => $q->where('branch_id', auth()->user()->branch_id));
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
        if (! $agent || (int) $agent->branch_id !== (int) $user->branch_id) {
            abort(404);
        }
    }

    /**
     * ?agent_id= from the URL is unvalidated input: a branch user could paste
     * another branch's id and have every tab filter on it. Resolve it inside
     * the caller's branch scope and 404 if it is not there.
     */
    private function authorizeAgentFilter(?string $agentId): void
    {
        if ($agentId === null || $agentId === '') {
            return;
        }

        $agent = $this->scopeAgentsByBranch(
            Agent::where('agency_id', auth()->user()->agency_id)
        )->find($agentId);

        $this->authorizeAgentBranch($agent);
    }

}
