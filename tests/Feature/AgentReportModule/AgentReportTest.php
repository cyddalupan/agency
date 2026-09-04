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

        $this->agency = Agency::factory()->create();
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

    private function makeReceivable(Agent $agent, float $amount, string $status = 'pending', ?Applicant $applicant = null): Receivable
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
            'account'        => 'Placement Fee',
            'debit_account'  => 'Receivable',
            'type'           => 'Full Payment',
            'mode'           => 'GCash',
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
    public function index_renders_the_seven_tabs(): void
    {
        $this->actingAs($this->user)->get(route('agent_report.index'))
            ->assertOk()
            ->assertSee('Commission')
            ->assertSee('Cash Advance')
            ->assertSee('Receivables')
            ->assertSee('Payments')
            ->assertSee('Deductions &amp; Paid', false)
            ->assertSee('Starting Balance')
            ->assertSee('Agent Report');
    }

    #[Test]
    public function each_tab_renders_with_an_agent_filter(): void
    {
        foreach (['commission', 'cash-advance', 'receivables', 'payments', 'deductions', 'starting-balances', 'report'] as $tab) {
            $this->actingAs($this->user)
                ->get(route('agent_report.index', ['tab' => $tab]))
                ->assertOk();
        }
    }

    #[Test]
    public function unknown_tab_falls_back_to_commission(): void
    {
        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'bogus']))
            ->assertOk();
    }

    // ---------- Receivables + Cash Advance tab routing ----------

    #[Test]
    public function receivable_accounts_appear_in_receivables_tab_and_not_commission(): void
    {
        $agent = $this->makeAgent();
        $this->makeAgentExpense($agent, 10000, 'PHP', null, 'Partial');
        $this->makeAgentExpense($agent, 20000, 'PHP', null, 'Full');
        $this->makeAgentExpense($agent, 30000, 'PHP', null, 'Deployed');
        $this->makeAgentExpense($agent, 40000, 'PHP', null, 'Other Commission');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'receivables']))
            ->assertOk()
            ->assertSee('Partial')
            ->assertSee('Full')
            ->assertSee('Deployed')
            ->assertDontSee('Other Commission');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'commission']))
            ->assertOk()
            ->assertSee('Other Commission')
            ->assertDontSee('Partial')
            ->assertDontSee('Full')
            ->assertDontSee('Deployed');
    }

    #[Test]
    public function cash_advance_accounts_appear_in_cash_advance_tab_and_not_commission(): void
    {
        $agent = $this->makeAgent();
        $this->makeAgentExpense($agent, 15000, 'PHP', null, 'Cash advance');
        $this->makeAgentExpense($agent, 25000, 'PHP', null, 'Other Commission');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'cash-advance']))
            ->assertOk()
            ->assertSee('Cash advance')
            ->assertDontSee('Other Commission');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'commission']))
            ->assertOk()
            ->assertSee('Other Commission')
            ->assertDontSee('Cash advance');
    }

    #[Test]
    public function receivable_and_cash_advance_tabs_respect_agent_filter(): void
    {
        $agentA = $this->makeAgent();
        $agentB = $this->makeAgent();

        $this->makeAgentExpense($agentA, 10000, 'PHP', null, 'Partial');
        $this->makeAgentExpense($agentB, 99999, 'PHP', null, 'Partial');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'receivables', 'agent_id' => $agentA->id]))
            ->assertOk()
            ->assertSee('10,000.00')
            ->assertDontSee('99,999.00');
    }

    // ---------- Report tab: new confirmed formula ----------

    #[Test]
    public function report_balance_follows_confirmed_formula(): void
    {
        $agent = $this->makeAgent();

        StartingBalance::create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'agent_id'  => $agent->id,
            'date'      => now()->toDateString(),
            'account'   => StartingBalance::ACCOUNT,
            'amount'    => 50000,
        ]);

        $this->makeAgentExpense($agent, 10000, 'PHP');      // commission
        $this->makeReceivable($agent, 4000, 'received');    // payment
        AgentDeduction::create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'agent_id'  => $agent->id,
            'date'      => now()->toDateString(),
            'account'   => AgentDeduction::ACCOUNT_DEDUCTION,
            'amount'    => 1500,
        ]);
        AgentDeduction::create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'agent_id'  => $agent->id,
            'date'      => now()->toDateString(),
            'account'   => AgentDeduction::ACCOUNT_PAID,
            'amount'    => 500,
        ]);

        // Balance = 50000 (SB) + 10000 (commission) + 4000 (payments) - 1500 (deduction) - 500 (paid) = 62000
        $this->actingAs($this->user)->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk()
            ->assertSee('50,000.00')   // starting balance
            ->assertSee('10,000.00')   // commission
            ->assertSee('4,000.00')    // payments
            ->assertSee('1,500.00')    // deductions
            ->assertSee('500.00')      // paid
            ->assertSee('62,000.00');  // net balance
    }

    #[Test]
    public function report_payments_count_only_received_receivables(): void
    {
        $agent = $this->makeAgent();

        $this->makeReceivable($agent, 6000, 'received');
        $this->makeReceivable($agent, 9000, 'pending'); // not counted as payment

        $this->actingAs($this->user)->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk()
            ->assertSee('6,000.00')
            ->assertDontSee('15,000.00');
    }

    #[Test]
    public function report_commission_converts_usd_to_php(): void
    {
        $agent = $this->makeAgent();

        $this->makeAgentExpense($agent, 10, 'USD'); // USD 10 -> PHP 560 @ 56

        $this->actingAs($this->user)->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk()
            ->assertSee('560.00'); // converted commission
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

        $this->makeReceivable($agentA, 5000, 'received');
        $this->makeReceivable($agentB, 99999, 'received');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'report', 'agent_id' => $agentA->id]))
            ->assertOk()
            ->assertSee('5,000.00')
            ->assertDontSee('99,999.00');
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
