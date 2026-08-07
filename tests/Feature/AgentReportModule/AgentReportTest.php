<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    }

    private function makeAgent(): Agent
    {
        return Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    private function makeApplicant(Agent $agent): Applicant
    {
        return Applicant::factory()->create([
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

    private function makeAgentExpense(Agent $agent, float $amount, string $currency = 'PHP', ?Applicant $applicant = null): ExpenseRequestItem
    {
        $er = ExpenseRequest::create([
            'agency_id'     => $this->agency->id,
            'user_id'       => $this->user->id,
            'reference_no'  => (string) rand(100000, 999999),
            'date'          => now()->toDateString(),
            'status'        => 'pending',
            'branch_id'     => $this->branch->id,
            'notes'         => 'test',
        ]);

        // agent sub-account
        $acct = Account::create([
            'agency_id'    => $this->agency->id,
            'name'         => 'Agent Advances',
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

    // ---------- Per-agent grouping + totals ----------

    #[Test]
    public function report_groups_ledger_by_agent_with_receivable_and_expense_totals(): void
    {
        $agentA = $this->makeAgent();
        $agentB = $this->makeAgent();

        // Agent A: 2 receivables (1 received/collected) + 1 agent expense
        $this->makeReceivable($agentA, 5000, 'pending');
        $this->makeReceivable($agentA, 3000, 'received');
        $this->makeAgentExpense($agentA, 1000, 'PHP');

        // Agent B: 1 receivable, uncollected
        $this->makeReceivable($agentB, 20000, 'pending');

        $resp = $this->actingAs($this->user)->get(route('agent_report.index'));
        $resp->assertOk();

        $resp->assertSee($agentA->name);
        $resp->assertSee($agentB->name);

        // Agent A rows visible with counts/totals
        $resp->assertSee('2');          // # receivables A (assert via data or rendered)
        $resp->assertSee('8,000.00');   // total receivable A (5000+3000)
    }

    #[Test]
    public function balance_is_calculated_as_receivables_minus_collected_minus_expenses(): void
    {
        $agent = $this->makeAgent();

        $this->makeReceivable($agent, 10000, 'pending');   // receivable
        $this->makeReceivable($agent, 4000, 'received');   // collected
        $this->makeAgentExpense($agent, 2500, 'PHP');      // expense

        $this->actingAs($this->user)->get(route('agent_report.index'))
            ->assertOk()
            ->assertSee('14,000.00')   // total receivable (10000 + 4000)
            ->assertSee('4,000.00')    // collected
            ->assertSee('2,500.00')    // expenses
            ->assertSee('7,500.00');   // balance = 14000 - 4000 - 2500
    }

    #[Test]
    public function usd_expenses_are_converted_to_php_equivalent_in_totals(): void
    {
        $agent = $this->makeAgent();

        $this->makeAgentExpense($agent, 10, 'USD'); // USD 10 -> PHP equivalent via config
        $this->makeReceivable($agent, 1000, 'pending');

        $rate = (float) config('expense.usd_to_php', 56);
        $peso = 1000 - ($rate * 10);

        $this->actingAs($this->user)->get(route('agent_report.index'))
            ->assertOk()
            ->assertSee(number_format($peso, 2));
    }

    #[Test]
    public function date_range_filter_limits_receivables_and_expenses(): void
    {
        $agent = $this->makeAgent();

        $this->makeReceivable($agent, 9000, 'pending'); // today

        $old = now()->subMonths(3);
        Receivable::create([
            'agency_id'      => $this->agency->id,
            'user_id'        => $this->user->id,
            'agent_id'       => $agent->id,
            'applicant_id'   => $this->makeApplicant($agent)->id,
            'code'           => '000099',
            'date'           => $old->toDateString(),
            'status'         => 'pending',
            'amount'         => 50000,
            'account'        => 'Placement Fee',
            'debit_account'  => 'Receivable',
            'type'           => 'Full Payment',
            'mode'           => 'GCash',
        ]);

        // filter only the last 30 days -> excludes the 50k old receivable
        $from = now()->startOfMonth()->toDateString();
        $to   = now()->endOfMonth()->toDateString();

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['from' => $from, 'to' => $to]))
            ->assertOk()
            ->assertSee('9,000.00')
            ->assertDontSee('50,000.00');
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
        $this->makeReceivable($otherAgent, 999999, 'pending');

        $this->actingAs($this->user)->get(route('agent_report.index'))
            ->assertOk()
            ->assertDontSee('999,999.00')
            ->assertDontSee($otherAgent->name);
    }
}
