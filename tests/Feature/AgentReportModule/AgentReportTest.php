<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\Receivable;
use App\Models\StartingBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AgentReportTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        // Force a deterministic FX rate (CurrencyConverter reads this cache key).
        Cache::put('fx_usd_to_php', 56.0, 3600);
    }

    private function makeAgent(): Agent
    {
        return Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    private function makeApplicant(Agent $agent, int $statusCode = 0): Applicant
    {
        return Applicant::factory()->withStatus($statusCode)->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $agent->id,
        ]);
    }

    /**
     * @param  string  $account  The ledger reads Payments from Receivables whose
     *                           account is 'Agents & Applicant Payment'. Other
     *                           suites use the historical 'Placement Fee', so the
     *                           default is unchanged and the ledger tests opt in.
     */
    private function makeReceivable(Agent $agent, float $amount, string $status = 'pending', ?Applicant $applicant = null, string $account = 'Placement Fee'): Receivable
    {
        return Receivable::create([
            'agency_id'      => $this->agency->id,
            'user_id'        => $this->user->id,
            'agent_id'       => $agent->id,
            'applicant_id'   => $applicant ? $applicant->id : $this->makeApplicant($agent)->id,
            'code'           => str_pad((string) ((int) (Receivable::max('id') ?? 0) + 1), 6, '0', STR_PAD_LEFT),
            'date'           => now()->toDateString(),
            'status'         => $status,
            'amount'         => $amount,
            'account'        => $account,
            'debit_account'  => 'Receivable',
            'type'           => 'Full Payment',
            'mode'           => 'GCash',
        ]);
    }

    /**
     * Backout & Repat and Payments on the Agent Ledger come from AgentDeduction
     * rows, NOT from expense items — see AgentReportController::buildReportRows().
     * A deduction line with account 'Deduction' sums into Backout & Repat; one
     * with account 'Paid' sums into Payments.
     */
    private function makeDeduction(Agent $agent, float $amount, string $account, string $particular = 'test'): AgentDeduction
    {
        return AgentDeduction::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'date'         => now()->toDateString(),
            'account'      => $account,
            'agent_id'     => $agent->id,
            'applicant_id' => null,
            'amount'       => $amount,
            'particular'   => $particular,
        ]);
    }

    private function makeAgentExpense(Agent $agent, float $amount, string $currency = 'PHP', ?Applicant $applicant = null, string $accountName = 'Agent Advances', string $requestStatus = 'pending'): ExpenseRequestItem
    {
        $er = ExpenseRequest::create([
            'agency_id'     => $this->agency->id,
            'user_id'       => $this->user->id,
            'reference_no'  => (string) rand(100000, 999999),
            'date'          => now()->toDateString(),
            'status'        => $requestStatus,
            'branch_id'     => $this->branch->id,
            'notes'         => 'test',
        ]);

        $acct = Account::create([
            'agency_id'    => $this->agency->id,
            'name'         => $accountName,
            'type'         => 'expense',
            'charge_type'  => 'agent',
            'parent_id'    => null,
        ]);

        return ExpenseRequestItem::create([
            'expense_request_id' => $er->id,
            'charge'             => 'agent',
            'agent_id'           => $agent->id,
            'applicant_id'       => $applicant ? $applicant->id : $this->makeApplicant($agent)->id,
            'country_id'         => Country::factory()->create()->id,
            'currency'           => $currency,
            'amount'             => $amount,
            'account_id'         => $acct->id,
            'particular'         => 'test',
        ]);
    }

    // ---------- Access control ----------

    #[Test]
    public function unauthenticated_user_cannot_access_agents_report(): void
    {
        $this->get(route('agent_report.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function unauthorized_role_cannot_access_agents_report(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $this->actingAs($staff)
            ->get(route('agent_report.index'))
            ->assertForbidden();
    }

    // ---------- Tabs render ----------

    #[Test]
    public function index_renders_the_tabs(): void
    {
        // The bar grew to 8 tabs when Return Payment was split out of
        // "Deductions & Paid" (Cyd 2026-11-04). This test still expected the
        // old combined label, so it had been failing ever since.
        $this->actingAs($this->user)->get(route('agent_report.index'))
            ->assertOk()
            ->assertSee('Commission')
            ->assertSee('Cash Advance')
            ->assertSee('Receivables')
            ->assertSee('Return Payment')
            ->assertSee('Payments')
            ->assertSee('Deductions')
            ->assertSee('Starting Balance')
            ->assertSee('Agent Ledger');
    }

    #[Test]
    public function each_tab_renders_with_an_agent_filter(): void
    {
        foreach (['cash-advance', 'receivables', 'payments', 'deductions', 'starting-balances', 'report'] as $tab) {
            $this->actingAs($this->user)
                ->get(route('agent_report.index', ['tab' => $tab]))
                ->assertOk();
        }
    }

    #[Test]
    public function unknown_tab_falls_back_to_the_agent_ledger(): void
    {
        // The Commission tab was removed (card 6afae127), so the invalid-tab
        // fallback must land on DEFAULT_TAB rather than on TABS[0].
        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'bogus']))
            ->assertOk()
            ->assertSee(agent_term().' Ledger');
    }

    #[Test]
    public function a_stale_commission_bookmark_lands_on_the_agent_ledger(): void
    {
        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'commission']))
            ->assertOk()
            ->assertSee(agent_term().' Ledger')
            ->assertDontSee('No commission transactions yet.');
    }

    #[Test]
    public function the_commission_tab_is_no_longer_offered(): void
    {
        $this->actingAs($this->user)
            ->get(route('agent_report.index'))
            ->assertOk()
            ->assertDontSee('💼 Commission');
    }

    // ---------- Receivables tab routing ----------

    #[Test]
    public function receivables_tab_shows_received_agents_and_applicant_payment_receivables(): void
    {
        // Card 6afb09e9: the tab reads the Receivable module, not expense
        // requests. One row that belongs, one that is the wrong status, one
        // that is the wrong account.
        $agent = $this->makeAgent();
        $this->makeReceivable($agent, 4000, 'received', null, 'Agents & Applicant Payment');
        $this->makeReceivable($agent, 7777, 'pending', null, 'Agents & Applicant Payment');
        $this->makeReceivable($agent, 5555, 'received', null, 'Placement Fee');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'receivables']))
            ->assertOk()
            ->assertSee('4,000.00')
            ->assertDontSee('7,777.00')   // pending is not money collected
            ->assertDontSee('5,555.00');  // wrong account
    }

    #[Test]
    public function receivables_tab_does_not_show_pending_agent_expense_requests(): void
    {
        // The regression this card exists to fix: PENDING agent-charged expense
        // items used to be listed here, which is the wrong table entirely —
        // they are expense requests, not receivables, and they also belong to
        // the Receivable module only by accident of an old tab.
        $agent = $this->makeAgent();
        $this->makeAgentExpense($agent, 12345, 'PHP', null, 'Deployed', 'pending');
        $this->makeAgentExpense($agent, 54321, 'PHP', null, 'Fit to work', 'pending');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'receivables']))
            ->assertOk()
            ->assertDontSee('12,345.00')
            ->assertDontSee('54,321.00')
            ->assertSee('No received receivables');
    }

    #[Test]
    public function receivables_tab_respects_agent_filter(): void
    {
        $agentA = $this->makeAgent();
        $agentB = $this->makeAgent();

        $this->makeReceivable($agentA, 10000, 'received', null, 'Agents & Applicant Payment');
        $this->makeReceivable($agentB, 99999, 'received', null, 'Agents & Applicant Payment');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'receivables', 'agent_id' => $agentA->id]))
            ->assertOk()
            ->assertSee('10,000.00')
            ->assertDontSee('99,999.00');
    }

    #[Test]
    public function cash_advance_accounts_appear_in_cash_advance_tab(): void
    {
        $agent = $this->makeAgent();
        $this->makeAgentExpense($agent, 15000, 'PHP', null, 'Cash advance');
        $this->makeAgentExpense($agent, 25000, 'PHP', null, 'Other Commission');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'cash-advance']))
            ->assertOk()
            ->assertSee('Cash advance')
            ->assertDontSee('Other Commission');

        // Was asserted against the removed Commission tab. Keep the half that
        // still means something: the non-cash-advance row must not leak into
        // the Cash Advance tab's figures.
        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'cash-advance']))
            ->assertOk()
            ->assertDontSee('25,000.00');
    }

    // ---------- Cash Advance tab routing ----------

    // ---------- Report tab: new confirmed formula ----------

    #[Test]
    public function agent_ledger_balance_follows_spec_formula(): void
    {
        $agent = $this->makeAgent();

        // Ledger semantics (AgentReportController::buildReportRows + agentLedgerData):
        //  commission   = RELEASED expense items on a commission account
        //  cash advance = RELEASED expense items on a cash-advance account
        //  backout/repat= AgentDeduction rows with account 'Deduction'
        //  receivable   = RECEIVED Receivables on 'Agents & Applicant Payment'
        //  payments     = AgentDeduction rows with account 'Paid'
        // The ledger ignores PENDING requests — do not relax that filter to make
        // a test pass; a pending row is not real money yet.
        $this->makeAgentExpense($agent, 10000, 'PHP', null, 'Fit to work', 'released');    // Total Commission
        $this->makeAgentExpense($agent, 2000, 'PHP', null, 'Agent Advances', 'released');  // Total Cash Advance
        $this->makeDeduction($agent, 3000, AgentDeduction::ACCOUNT_DEDUCTION);            // Total Backout and Repat
        $this->makeReceivable($agent, 4000, 'received', null, 'Agents & Applicant Payment'); // Total Receivable (AR)
        $this->makeDeduction($agent, 5000, AgentDeduction::ACCOUNT_PAID);                 // Total Payments

        // Balance = 2000 (CA) + 3000 (backout & repat) - 4000 (receivable AR) - 5000 (payments) = -4000
        $this->actingAs($this->user)->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk()
            ->assertSee('10,000.00')   // total commission
            ->assertSee('2,000.00')    // total cash advance
            ->assertSee('3,000.00')    // total backout and repat
            ->assertSee('4,000.00')    // total receivable (AR)
            ->assertSee('5,000.00')    // total payments
            ->assertSee('-4,000.00');  // agent's balance
    }

    #[Test]
    public function report_payments_count_only_received_receivables(): void
    {
        $agent = $this->makeAgent();

        // The ledger reads Payments from AgentDeduction rows with account 'Paid'.
        // A pending Receivable is NOT a payment — only a RECEIVED one counts,
        // and then it lands in the AR column, never in Payments.
        $this->makeDeduction($agent, 6000, AgentDeduction::ACCOUNT_PAID);

        // One RECEIVED receivable. This is what makes the column separation
        // observable: if receipts ever leak into Payments, AR and Payments
        // stop being distinguishable and this test goes red.
        $this->makeReceivable($agent, 2500, 'received', null, 'Agents & Applicant Payment');

        // Pending receivables of arbitrary, deliberately unrelated amounts.
        // None of these may move ANY figure. Several different amounts mean no
        // single magic sum can stand in for the property: whatever the ledger
        // adds up, it cannot equal one of these by luck.
        $this->makeReceivable($agent, 9000, 'pending', null, 'Agents & Applicant Payment');
        $this->makeReceivable($agent, 1234, 'pending', null, 'Agents & Applicant Payment');
        $this->makeReceivable($agent, 77, 'pending', null, 'Agents & Applicant Payment');

        // Assert the ledger ROW in column order, not a number found anywhere on
        // the page. Columns: commission, cash advance, backout/repat,
        // receivable (AR), return payment, payments, balance.
        //   Payments = 6,000.00 (the Paid deduction only)
        //   AR       = 2,500.00 (the received receivable only)
        //   Balance  = 0 + 0 - 2,500 + 0 - 6,000 = -8,500.00
        // If either pending receivable were counted, AR and/or Payments would
        // move and this row would not match.
        $response = $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk();

        $this->assertSame([
            '₱0.00',       // commission
            '₱0.00',       // cash advance
            '₱0.00',       // backout and repat
            '₱2,500.00',   // receivable (AR) — the received row only
            '₱0.00',       // return payment
            '₱6,000.00',   // payments — the Paid deduction only
            '₱-8,500.00',  // balance
        ], $this->ledgerRow($response->getContent(), $agent->name));
    }

    #[Test]
    public function report_commission_converts_usd_to_php(): void
    {
        $agent = $this->makeAgent();

        // The ledger reads commission from a commission account — 'Fit to work'
        // is one of RECEIVABLE_ACCOUNT_NAMES. 'Other Commission' is not.
        $this->makeAgentExpense($agent, 10, 'USD', null, 'Fit to work', 'released'); // USD 10 -> PHP 560 @ 56

        // Assert the commission COLUMN of the row, not a number anywhere on the
        // page: a bare assertSee('560.00') would also match an amount that had
        // leaked into cash advance, AR, payments or the balance. Column order is
        // commission, cash advance, backout/repat, AR, return, payments, balance.
        $response = $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk();

        $this->assertSame([
            '₱560.00',  // commission — converted from USD 10 @ 56
            '₱0.00',    // cash advance
            '₱0.00',    // backout and repat
            '₱0.00',    // AR
            '₱0.00',    // return payment
            '₱0.00',    // payments
            '₱0.00',    // balance
        ], $this->ledgerRow($response->getContent(), $agent->name));
    }

    #[Test]
    public function zero_activity_agents_are_still_listed(): void
    {
        $agent = $this->makeAgent(); // no receivables, expenses, commissions

        $this->actingAs($this->user)->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk()
            ->assertSee($agent->name);
    }

    #[Test]
    public function report_filter_limits_to_selected_agent(): void
    {
        $agentA = $this->makeAgent();
        $agentB = $this->makeAgent();

        $this->makeReceivable($agentA, 5000, 'received', null, 'Agents & Applicant Payment');
        $this->makeReceivable($agentB, 99999, 'received', null, 'Agents & Applicant Payment');

        // Assert on the ledger TABLE, not on numbers found anywhere in the page.
        // assertSee('5,000.00') alone would pass even if the filter did nothing,
        // because agent A's row is rendered either way. The property is that the
        // ledger body contains exactly one agent row — A's — and B never appears.
        // (B's name DOES legitimately appear in the filter dropdown, so the
        // assertion is scoped to <tbody>, not the whole page.)
        $response = $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'report', 'agent_id' => $agentA->id]))
            ->assertOk();

        $tbody = $this->ledgerTbody($response->getContent());
        $this->assertStringContainsString($agentA->name, $tbody, 'selected agent must be in the ledger');
        $this->assertStringNotContainsString($agentB->name, $tbody, 'filtered-out agent must not be in the ledger');

        // A's AR is 5,000.00; if B's 99,999.00 had been folded in it would read
        // 104,999.00, and A's balance would move off -5,000.00.
        $this->assertSame([
            '₱0.00',       // commission
            '₱0.00',       // cash advance
            '₱0.00',       // backout and repat
            '₱5,000.00',   // AR — agent A only
            '₱0.00',       // return payment
            '₱0.00',       // payments
            '₱-5,000.00',  // balance = -AR
        ], $this->ledgerRow($response->getContent(), $agentA->name));
    }

    // ---------- Single-agent ledger detail view + PDF (clickable Name) ----------

    #[Test]
    public function detail_view_shows_agent_name_branch_and_tables(): void
    {
        $agent = $this->makeAgent();
        $this->makeAgentExpense($agent, 3000, 'PHP', null, 'Partial', 'released');

        $this->actingAs($this->user)
            ->get(route('agent_report.show', $agent))
            ->assertOk()
            ->assertSee($agent->name)
            ->assertSee('AGENT LEDGER', false)
            ->assertSee('Released Commission')
            ->assertSee('Summary Report');
    }

    #[Test]
    public function detail_view_prints_a_downloadable_pdf_page(): void
    {
        $agent = $this->makeAgent();
        $this->makeAgentExpense($agent, 1500, 'PHP', null, 'Contract', 'released');

        $this->actingAs($this->user)
            ->get(route('agent_report.show.print', $agent))
            ->assertOk()
            ->assertSee($agent->name)
            ->assertSee('AGENT LEDGER', false)
            ->assertSee('1,500.00');
    }

    #[Test]
    public function detail_view_isolates_agents_by_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAgent = Agent::factory()->create([
            'agency_id' => $otherAgency->id,
            'branch_id' => Branch::factory()->create(['agency_id' => $otherAgency->id])->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('agent_report.show', $otherAgent))
            ->assertNotFound();
    }

    #[Test]
    public function ledger_table_agent_name_links_to_detail_view(): void
    {
        $agent = $this->makeAgent();
        $this->makeReceivable($agent, 1000, 'received');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk()
            ->assertSee(route('agent_report.show', $agent->id), false);
    }

    // ---------- Agency isolation ----------

    #[Test]
    public function agents_report_is_scoped_to_current_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAgent = Agent::factory()->create([
            'agency_id' => $otherAgency->id,
            'branch_id' => Branch::factory()->create(['agency_id' => $otherAgency->id])->id,
        ]);
        $this->makeReceivable($otherAgent, 999999, 'received');

        $this->actingAs($this->user)->get(route('agent_report.index'))
            ->assertOk()
            ->assertDontSee('999,999.00')
            ->assertDontSee($otherAgent->name);
    }
}
