<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\StartingBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StartingBalanceTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function makeAgent(): Agent
    {
        return Agent::factory()->create(['agency_id' => $this->agency->id]);
    }

    private function validPayload(Agent $agent, array $overrides = []): array
    {
        return array_merge([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'date'         => now()->toDateString(),
            'account'      => StartingBalance::ACCOUNT,
            'agent_id'     => $agent->id,
            'applicant_id' => null,
            'amount'       => 50000.00,
            'particular'   => 'Opening balance',
        ], $overrides);
    }

    // ---------- Access control ----------

    #[Test]
    public function unauthenticated_user_cannot_access_starting_balance_create(): void
    {
        $this->get(route('agent_report.starting_balance.create'))->assertRedirect(route('login'));
    }

    #[Test]
    public function unauthorized_role_cannot_access_starting_balance_create(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $this->actingAs($staff)
            ->get(route('agent_report.starting_balance.create'))
            ->assertForbidden();
    }

    // ---------- Store ----------

    #[Test]
    public function store_creates_a_starting_balance_with_fixed_account(): void
    {
        $agent = $this->makeAgent();

        $this->actingAs($this->user)
            ->post(route('agent_report.starting_balance.store'), $this->validPayload($agent))
            ->assertRedirect();

        $this->assertDatabaseHas('starting_balances', [
            'agency_id'  => $this->agency->id,
            'user_id'    => $this->user->id,
            'agent_id'   => $agent->id,
            'account'    => 'Starting Balance',
            'amount'     => 50000.00,
            'particular' => 'Opening balance',
        ]);
    }

    #[Test]
    public function only_one_starting_balance_per_agent_is_allowed(): void
    {
        $agent = $this->makeAgent();

        $this->actingAs($this->user)
            ->post(route('agent_report.starting_balance.store'), $this->validPayload($agent))
            ->assertRedirect();

        $this->actingAs($this->user)
            ->post(route('agent_report.starting_balance.store'), $this->validPayload($agent, ['amount' => 90000.00]))
            ->assertSessionHasErrors('agent_id');

        $this->assertSame(1, StartingBalance::where('agent_id', $agent->id)->count());
    }

    #[Test]
    public function amount_is_required_and_positive(): void
    {
        $agent = $this->makeAgent();

        $this->actingAs($this->user)
            ->post(route('agent_report.starting_balance.store'), $this->validPayload($agent, ['amount' => 0]))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('starting_balances', 0);
    }

    #[Test]
    public function agent_must_belong_to_the_same_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAgent = Agent::factory()->create(['agency_id' => $otherAgency->id]);

        $this->actingAs($this->user)
            ->post(route('agent_report.starting_balance.store'), $this->validPayload($otherAgent))
            ->assertNotFound(); // agency mismatch -> 404 (Receivable pattern)

        $this->assertDatabaseCount('starting_balances', 0);
    }

    #[Test]
    public function applicant_must_belong_to_the_selected_agent(): void
    {
        $agentA = $this->makeAgent();
        $agentB = $this->makeAgent();
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $agentB->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('agent_report.starting_balance.store'), $this->validPayload($agentA, ['applicant_id' => $applicant->id]))
            ->assertSessionHasErrors('applicant_id');

        $this->assertDatabaseCount('starting_balances', 0);
    }

    // ---------- List / tab ----------

    #[Test]
    public function starting_balance_tab_lists_entries_and_filters_by_agent(): void
    {
        $agentA = $this->makeAgent();
        $agentB = $this->makeAgent();

        StartingBalance::create($this->validPayload($agentA, ['particular' => 'Opening for A']));
        StartingBalance::create($this->validPayload($agentB, ['particular' => 'Opening for B']));

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'starting-balances']))
            ->assertOk()
            ->assertSee('Opening for A')
            ->assertSee('Opening for B');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'starting-balances', 'agent_id' => $agentA->id]))
            ->assertOk()
            ->assertSee('Opening for A')
            ->assertDontSee('Opening for B');
    }

    // ---------- Agency isolation ----------

    #[Test]
    public function starting_balances_are_scoped_to_current_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAgent = Agent::factory()->create(['agency_id' => $otherAgency->id]);
        StartingBalance::create($this->validPayload($otherAgent, [
            'agency_id' => $otherAgency->id,
            'particular' => 'Foreign opening',
        ]));

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'starting-balances']))
            ->assertOk()
            ->assertDontSee('Foreign opening');
    }
}
