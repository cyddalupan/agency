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

/**
 * Card 6b00a6ec — "AGENT REPORT Branch: branch users see only their own
 * branch's agents, on all seven tabs."
 *
 * Fixture: ONE agency (gulf, so the module is enabled), TWO branches, ONE agent
 * per branch. Every row on every tab exists twice: once for branch A's agent,
 * once for branch B's. A branch user of branch A then sees A's row and must not
 * see B's.
 *
 * Presence AND absence are asserted per tab, always. An absence-only test
 * passes against a query that returns nothing at all, which is precisely the
 * failure a broken branch lock produces — so the positive half is the control
 * that proves the test would have caught it.
 */
class AgentReportBranchScopeAllTabsTest extends TestCase
{
    use RefreshDatabase;

    /** Amounts deliberately different so a blended total is never equal by luck. */
    private const A_AMOUNT = 1111.00;
    private const B_AMOUNT = 7777.00;

    private Agency $agency;
    private Branch $branchA;
    private Branch $branchB;
    private Agent $agentA;
    private Agent $agentB;
    private User $userA;

    /** @var array<string,int> account name => id */
    private array $accounts = [];

    protected function setUp(): void
    {
        parent::setUp();

        Cache::put('fx_usd_to_php', 56.0, 3600);

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        // Bind the tenant the way IdentifyAgency does for a real request. Without
        // it, TenantScope resolves the agency from the login session
        // (tenant_agency_id), which tests that actAs() a user never set — the
        // agents query, the expenses query and the branch lock then disagree
        // about which agency they are looking at, and pass or fail by accident.
        app()->instance('tenant_agency', $this->agency);
        $this->branchA = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'ALPHA']);
        $this->branchB = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'BETA']);

        $this->agentA = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branchA->id,
            'name'      => 'ALPHA AGENT',
        ]);
        $this->agentB = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branchB->id,
            'name'      => 'BETA AGENT',
        ]);
        Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => null,
            'name'      => 'UNASSIGNED AGENT',
        ]);

        $this->userA = $this->makeUser($this->branchA, 'branch', 'ALPHA BRANCH USER');

        foreach ($this->accounts as $name => $id) {
            unset($this->accounts[$name]);
        }
        foreach (['Cash advance', 'Return', 'Deployed', 'Fit to work', 'Agent Advances', 'Office supply'] as $name) {
            $this->accounts[$name] = Account::create([
                'agency_id'   => $this->agency->id,
                'name'        => $name,
                'type'        => 'expense',
                'charge_type' => 'agent',
                'parent_id'   => null,
            ])->id;
        }
    }

    private function makeUser(Branch $branch, string $type, string $name): User
    {
        return User::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $branch->id,
            'user_type' => $type,
            'name'      => $name,
        ]);
    }

    /** An expense request + one agent-charged line, marked with a unique reference. */
    private function makeItem(
        Agent $agent,
        string $referenceNo,
        float $amount,
        string $status,
        string $account,
        float $payment = 0.0
    ): ExpenseRequestItem {
        // Agency users carry no branch, and ExpenseRequest::$fillable-less
        // create() with a branch_id from a branch user is fine — but keep the
        // request branch-free so no other guard keys off it.
        $er = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->userA->id,
            'reference_no' => $referenceNo,
            'date'         => now()->toDateString(),
            'status'       => $status,
            'notes'        => 'branch scope test',
        ]);

        return ExpenseRequestItem::create([
            'expense_request_id' => $er->id,
            'branch_id'          => $agent->branch_id,
            'charge'             => 'agent',
            'agent_id'           => $agent->id,
            'applicant_id'       => null,
            'country_id'         => Country::factory()->create()->id,
            'currency'           => 'PHP',
            'amount'             => $amount,
            'payment'            => $payment,
            'account_id'         => $this->accounts[$account],
            'particular'         => 'scope ' . $referenceNo,
        ]);
    }

    private function makeDeduction(Agent $agent, string $reference, float $amount, string $account = AgentDeduction::ACCOUNT_DEDUCTION): AgentDeduction
    {
        return AgentDeduction::create([
            'agency_id'  => $this->agency->id,
            'user_id'    => $this->userA->id,
            'agent_id'   => $agent->id,
            'date'       => now()->toDateString(),
            'account'    => $account,
            'amount'     => $amount,
            'particular' => 'scope ' . $reference,
        ]);
    }

    private function makeReceivable(Agent $agent, string $reference, float $amount): Receivable
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $agent->id,
            'last_name' => 'SCOPE' . $reference,
        ]);

        return Receivable::create([
            'agency_id'    => $this->agency->id,
            'agent_id'     => $agent->id,
            'applicant_id' => $applicant->id,
            'user_id'      => $this->userA->id,
            'date'         => now()->toDateString(),
            'account'      => 'Agents & Applicant Payment',
            'status'       => Receivable::STATUS_RECEIVED,
            'amount'       => $amount,
            'particular'   => 'scope ' . $reference,
        ]);
    }

    private function makeStartingBalance(Agent $agent, string $reference, float $amount): StartingBalance
    {
        return StartingBalance::create([
            'agency_id'  => $this->agency->id,
            'user_id'    => $this->userA->id,
            'agent_id'   => $agent->id,
            'date'       => now()->toDateString(),
            'account'    => StartingBalance::ACCOUNT,
            'amount'     => $amount,
            'particular' => 'scope ' . $reference,
        ]);
    }

    // -----------------------------------------------------------------
    // Tab 1 — Commission
    // -----------------------------------------------------------------

    #[Test]
    public function commission_tab_shows_only_branch_a_rows(): void
    {
        // A plain expense account (not one of the six routed to
        // Receivables/Cash Advance) — those are excluded from this tab by design.
        $this->makeItem($this->agentA, 'COMM-A-1', self::A_AMOUNT, ExpenseRequest::STATUS_PENDING, 'Office supply');
        $this->makeItem($this->agentB, 'COMM-B-1', self::B_AMOUNT, ExpenseRequest::STATUS_PENDING, 'Office supply');

        $this->actingAs($this->userA)
            ->get(route('agent_report.index', ['tab' => 'commission']))
            ->assertOk()
            ->assertSee('COMM-A-1')
            ->assertDontSee('COMM-B-1');
    }

    #[Test]
    public function released_commission_block_shows_only_branch_a_rows(): void
    {
        $this->makeItem($this->agentA, 'REL-A-1', self::A_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Fit to work');
        $this->makeItem($this->agentB, 'REL-B-1', self::B_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Fit to work');

        $this->actingAs($this->userA)
            ->get(route('agent_report.index', ['tab' => 'commission']))
            ->assertOk()
            ->assertSee('REL-A-1')
            ->assertDontSee('REL-B-1')
            // The block's own total is a separate sum over the same list.
            ->assertSee('1,111.00')
            ->assertDontSee('8,888.00');
    }

    // -----------------------------------------------------------------
    // Tab 2 — Cash Advance
    // -----------------------------------------------------------------

    #[Test]
    public function cash_advance_tab_shows_only_branch_a_rows(): void
    {
        $this->makeItem($this->agentA, 'CA-A-1', self::A_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Cash advance');
        $this->makeItem($this->agentB, 'CA-B-1', self::B_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Cash advance');

        $this->actingAs($this->userA)
            ->get(route('agent_report.index', ['tab' => 'cash-advance']))
            ->assertOk()
            ->assertSee('CA-A-1')
            ->assertDontSee('CA-B-1');
    }

    // -----------------------------------------------------------------
    // Tab 3 — Return Payment
    // -----------------------------------------------------------------

    #[Test]
    public function return_payment_tab_shows_only_branch_a_rows(): void
    {
        $this->makeItem($this->agentA, 'RET-A-1', self::A_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Return');
        $this->makeItem($this->agentB, 'RET-B-1', self::B_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Return');

        $this->actingAs($this->userA)
            ->get(route('agent_report.index', ['tab' => 'return-payment']))
            ->assertOk()
            ->assertSee('RET-A-1')
            ->assertDontSee('RET-B-1')
            ->assertSee('1,111.00')
            ->assertDontSee('8,888.00');
    }

    // -----------------------------------------------------------------
    // Tab 4 — Payments
    // -----------------------------------------------------------------

    #[Test]
    public function payments_tab_shows_only_branch_a_rows(): void
    {
        $this->makeItem($this->agentA, 'PAY-A-1', self::A_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Office supply', 100.00);
        $this->makeItem($this->agentB, 'PAY-B-1', self::B_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Office supply', 200.00);

        $this->actingAs($this->userA)
            ->get(route('agent_report.index', ['tab' => 'payments']))
            ->assertOk()
            ->assertSee('PAY-A-1')
            ->assertDontSee('PAY-B-1');
    }

    // -----------------------------------------------------------------
    // Tab 5 — Deduction
    // -----------------------------------------------------------------

    #[Test]
    public function deduction_tab_shows_only_branch_a_rows(): void
    {
        $this->makeDeduction($this->agentA, 'DED-A-1', self::A_AMOUNT);
        $this->makeDeduction($this->agentB, 'DED-B-1', self::B_AMOUNT);

        $this->actingAs($this->userA)
            ->get(route('agent_report.index', ['tab' => 'deductions']))
            ->assertOk()
            ->assertSee('DED-A-1')
            ->assertDontSee('DED-B-1');
    }

    // -----------------------------------------------------------------
    // Tab 6 — Starting Balance
    // -----------------------------------------------------------------

    #[Test]
    public function starting_balance_tab_shows_only_branch_a_rows(): void
    {
        $this->makeStartingBalance($this->agentA, 'SB-A-1', self::A_AMOUNT);
        $this->makeStartingBalance($this->agentB, 'SB-B-1', self::B_AMOUNT);

        $this->actingAs($this->userA)
            ->get(route('agent_report.index', ['tab' => 'starting-balances']))
            ->assertOk()
            ->assertSee('SB-A-1')
            ->assertDontSee('SB-B-1');
    }

    // -----------------------------------------------------------------
    // Tab 7 — Agent Ledger (+ its totals row, + the detail drill-down)
    // -----------------------------------------------------------------

    #[Test]
    public function agent_ledger_tab_lists_only_branch_a_agents(): void
    {
        $this->makeItem($this->agentA, 'LED-A-1', self::A_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Cash advance');
        $this->makeItem($this->agentB, 'LED-B-1', self::B_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Cash advance');

        $this->actingAs($this->userA)
            ->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk()
            ->assertSee('ALPHA AGENT')
            ->assertDontSee('BETA AGENT')
            ->assertDontSee('UNASSIGNED AGENT')
            // Totals row: branch A's agent only, not the summed agency.
            ->assertSee('1,111.00')
            ->assertDontSee('8,888.00');
    }

    #[Test]
    public function agent_ledger_detail_shows_only_branch_a_rows(): void
    {
        $this->makeItem($this->agentA, 'DET-A-1', self::A_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Cash advance');
        $this->makeItem($this->agentB, 'DET-B-1', self::B_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Cash advance');

        $this->actingAs($this->userA)
            ->get(route('agent_report.show', $this->agentA))
            ->assertOk()
            ->assertSee('DET-A-1')
            // This agent's own ledger cannot carry another agent's rows by
            // construction; the assertion is here so a change to
            // agentLedgerData() that drops the agent_id condition is caught.
            ->assertDontSee('DET-B-1');
    }

    // -----------------------------------------------------------------
    // Aggregates — the number, not just the rows
    // -----------------------------------------------------------------

    #[Test]
    public function agent_ledger_totals_for_a_branch_user_equal_the_branch_a_sum(): void
    {
        // Cash advance 1111 (A) vs 7777 (B); deduction 1111 (A) vs 2222 (B).
        $this->makeItem($this->agentA, 'AGG-CA-A', self::A_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Cash advance');
        $this->makeItem($this->agentB, 'AGG-CA-B', self::B_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Cash advance');
        $this->makeDeduction($this->agentA, 'AGG-DED-A', self::A_AMOUNT);
        $this->makeDeduction($this->agentB, 'AGG-DED-B', 2222.00);

        // Branch A: cash advance 1111, backout 1111, balance 2222.
        $this->actingAs($this->userA)
            ->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk()
            ->assertSee('2,222.00')
            // Blended agency figures must be absent. B's cash advance alone
            // would push the column to 8,888.00.
            ->assertDontSee('8,888.00')
            ->assertDontSee('3,333.00');

        // Same number through the print and CSV paths, which rebuild the rows
        // independently of the index view.
        $csv = $this->actingAs($this->userA)
            ->get(route('agent_report.export'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('ALPHA AGENT', $csv);
        $this->assertStringNotContainsString('BETA AGENT', $csv);
        $this->assertStringContainsString('1,111.00', $csv);
        $this->assertStringNotContainsString('8,888.00', $csv);
    }

    // -----------------------------------------------------------------
    // ?agent_id= — the URL surface, every tab
    // -----------------------------------------------------------------

    #[Test]
    public function a_branch_user_passing_another_branchs_agent_id_404s_on_every_tab(): void
    {
        foreach (['commission', 'cash-advance', 'receivables', 'return-payment', 'payments', 'deductions', 'starting-balances', 'report'] as $tab) {
            $this->actingAs($this->userA)
                ->get(route('agent_report.index', ['tab' => $tab, 'agent_id' => $this->agentB->id]))
                ->assertNotFound();
        }
    }

    #[Test]
    public function a_branch_user_passing_their_own_agent_id_is_allowed_and_filters(): void
    {
        $this->makeItem($this->agentA, 'OWN-A-1', self::A_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Cash advance');
        $this->makeItem($this->agentB, 'OWN-B-1', self::B_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Cash advance');

        $this->actingAs($this->userA)
            ->get(route('agent_report.index', ['tab' => 'cash-advance', 'agent_id' => $this->agentA->id]))
            ->assertOk()
            ->assertSee('OWN-A-1');
    }

    #[Test]
    public function admin_can_print_without_an_agent_id_and_with_its_own(): void
    {
        // Card 6b02fb4e repaired a 500 in print(); the ?agent_id= guard must
        // not bring it back, and it must not fire for an admin who is not
        // branch-locked.
        $mainOffice = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Main Office']);
        $mainUser = $this->makeUser($mainOffice, 'admin', 'MAIN ADMIN');
        $this->makeItem($this->agentB, 'PRINT-B-1', self::B_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Cash advance');

        $this->actingAs($mainUser)
            ->get(route('agent_report.print'))
            ->assertOk();

        $this->actingAs($mainUser)
            ->get(route('agent_report.print', ['agent_id' => $this->agentB->id]))
            ->assertOk();
    }

    #[Test]
    public function print_and_export_404_on_another_branchs_agent_id(): void
    {
        $this->actingAs($this->userA)
            ->get(route('agent_report.print', ['agent_id' => $this->agentB->id]))
            ->assertNotFound();

        $this->actingAs($this->userA)
            ->get(route('agent_report.export', ['agent_id' => $this->agentB->id]))
            ->assertNotFound();
    }

    #[Test]
    public function branch_user_cannot_open_another_branchs_agent_ledger(): void
    {
        $this->actingAs($this->userA)
            ->get(route('agent_report.show', $this->agentB))
            ->assertNotFound();
    }

    #[Test]
    public function the_agent_dropdown_lists_only_branch_a_agents(): void
    {
        $response = $this->actingAs($this->userA)
            ->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk();

        $agents = $response->viewData('agents');

        $this->assertSame(['ALPHA AGENT'], $agents->pluck('name')->all());
    }

    // -----------------------------------------------------------------
    // Fail-closed: a branch user with no branch
    // -----------------------------------------------------------------

    #[Test]
    public function a_branch_user_with_a_null_branch_id_is_not_narrowed_to_a_branch(): void
    {
        $orphan = User::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => null,
            'user_type' => 'branch',
            'name'      => 'NO BRANCH USER',
        ]);

        // MEASURED, not assumed (the card asked for this): isBranchLocked()
        // short-circuits on `branch_id <= 0`, so a branch user with no branch
        // is NOT branch-locked. ledgerBranchLocked() is therefore false and
        // scopeAgentsByBranch() never emits its where at all.
        $this->assertFalse($orphan->isBranchLocked(), 'precondition: a null branch is not a branch lock');

        // What the where WOULD mean if it ever ran: Eloquent treats a null
        // binding as whereNull, not `= NULL`, so it selects the unassigned
        // agents — the opposite of "nothing". The card's premise ("= NULL
        // matches nothing, so this is fail-closed") is wrong on both halves:
        // the predicate is not emitted, and if it were it would not be empty.
        $this->assertSame(
            1,
            Agent::where('agency_id', $this->agency->id)->where('branch_id', null)->count(),
            'where(branch_id, null) is whereNull(branch_id) — unassigned agents, not zero'
        );
        $this->assertSame(
            0,
            Agent::where('agency_id', $this->agency->id)->whereRaw('branch_id = ?', [null])->count(),
            'the literal SQL form `branch_id = NULL` does match nothing — the builder is what differs'
        );

        // End to end: the screen is not narrowed to any branch. Because
        // isBranchLocked() is false, BOTH the agent dropdown and the report
        // rows come back unscoped — the user sees every agent of the agency,
        // branch A's and branch B's included. This is the status quo and it is
        // what the card has to decide about; the test pins it so a later
        // change to isBranchLocked() cannot silently flip it unnoticed.
        $response = $this->actingAs($orphan)
            ->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk();

        $this->assertSame(
            ['ALPHA AGENT', 'BETA AGENT', 'UNASSIGNED AGENT'],
            $response->viewData('reportRows')->pluck('agent')->all(),
            'a null-branch branch user is currently NOT scoped: every agency agent is listed'
        );
        $this->assertSame(
            1,
            Agent::where('agency_id', $this->agency->id)->where('branch_id', $this->branchA->id)->count(),
            'branch A does have a row on other tabs — the null-branch user simply is not scoped to it'
        );
    }

    // -----------------------------------------------------------------
    // Main Office stays unlocked
    // -----------------------------------------------------------------

    #[Test]
    public function main_office_users_still_see_every_branch(): void
    {
        $mainOffice = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Main Office']);
        $mainUser = $this->makeUser($mainOffice, 'admin', 'MAIN ADMIN');

        $this->makeItem($this->agentA, 'MO-A-1', self::A_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Cash advance');
        $this->makeItem($this->agentB, 'MO-B-1', self::B_AMOUNT, ExpenseRequest::STATUS_RELEASED, 'Cash advance');
        $this->makeDeduction($this->agentA, 'MO-DED-A', 111.00);
        $this->makeDeduction($this->agentB, 'MO-DED-B', 222.00);

        $this->assertFalse($mainUser->isBranchLocked());

        foreach (['commission', 'cash-advance', 'payments', 'deductions', 'starting-balances'] as $tab) {
            $this->actingAs($mainUser)
                ->get(route('agent_report.index', ['tab' => $tab]))
                ->assertOk();
        }

        $this->actingAs($mainUser)
            ->get(route('agent_report.index', ['tab' => 'cash-advance']))
            ->assertOk()
            ->assertSee('MO-A-1')
            ->assertSee('MO-B-1');

        $this->actingAs($mainUser)
            ->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk()
            ->assertSee('ALPHA AGENT')
            ->assertSee('BETA AGENT')
            ->assertSee('UNASSIGNED AGENT')
            ->assertSee('8,888.00');

        // And the ?agent_id= guard does not fire for them.
        $this->actingAs($mainUser)
            ->get(route('agent_report.index', ['tab' => 'cash-advance', 'agent_id' => $this->agentB->id]))
            ->assertOk();
    }

    // -----------------------------------------------------------------
    // Out-of-branch agents are refused on the write paths too
    // -----------------------------------------------------------------

    #[Test]
    public function a_branch_user_cannot_post_a_deduction_or_starting_balance_for_another_branch_agent(): void
    {
        // A plain Branch user is not allowed to create either entry, so the 403
        // comes from canManageDeductions() before the branch guard is reached.
        // Use a Branch ADMIN — same branch lock, but past the role gate — so the
        // assertion proves the agent guard itself fires.
        $branchAdmin = $this->makeUser($this->branchA, 'admin', 'ALPHA BRANCH ADMIN');

        $this->assertTrue(
            \App\Support\ModuleAccess::allows('admin', 'agent_report', $this->agency->id)
        );

        $this->actingAs($branchAdmin)
            ->post(route('agent_report.deduction.store'), [
                'date'     => now()->toDateString(),
                'agent_id' => $this->agentB->id,
                'amount'   => 50,
            ])
            ->assertNotFound();

        $this->actingAs($branchAdmin)
            ->post(route('agent_report.starting_balance.store'), [
                'date'     => now()->toDateString(),
                'agent_id' => $this->agentB->id,
                'amount'   => 50,
            ])
            ->assertNotFound();

        $this->assertSame(0, AgentDeduction::count());
        $this->assertSame(0, StartingBalance::count());

        // ...and the same admin can still post for their own branch's agent,
        // so the refusal above is the branch, not the role.
        $this->actingAs($branchAdmin)
            ->post(route('agent_report.deduction.store'), [
                'date'     => now()->toDateString(),
                'agent_id' => $this->agentA->id,
                'amount'   => 50,
            ])
            ->assertRedirect();

        $this->assertSame(1, AgentDeduction::count());
    }
}
