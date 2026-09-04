<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AgentDeductionTest extends TestCase
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
            'account'      => AgentDeduction::ACCOUNT_DEDUCTION,
            'agent_id'     => $agent->id,
            'applicant_id' => null,
            'amount'       => 2500.00,
            'particular'   => 'Penalty for late placement',
        ], $overrides);
    }

    // ---------- Access control ----------

    #[Test]
    public function unauthenticated_user_cannot_access_deduction_create(): void
    {
        $this->get(route('agent_report.deduction.create'))->assertRedirect(route('login'));
    }

    #[Test]
    public function unauthorized_role_cannot_access_deduction_create(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $this->actingAs($staff)
            ->get(route('agent_report.deduction.create'))
            ->assertForbidden();
    }

    // ---------- Store ----------

    #[Test]
    public function store_creates_a_deduction_entry(): void
    {
        $agent = $this->makeAgent();

        $this->actingAs($this->user)
            ->post(route('agent_report.deduction.store'), $this->validPayload($agent))
            ->assertRedirect();

        $this->assertDatabaseHas('agent_deductions', [
            'agency_id'   => $this->agency->id,
            'user_id'     => $this->user->id,
            'agent_id'    => $agent->id,
            'account'     => AgentDeduction::ACCOUNT_DEDUCTION,
            'amount'      => 2500.00,
            'particular'  => 'Penalty for late placement',
        ]);
    }

    #[Test]
    public function store_always_forces_account_to_deduction(): void
    {
        $agent = $this->makeAgent();

        // The account dropdown is gone; even a stale 'Paid'/'Refund' payload saves as Deduction.
        $this->actingAs($this->user)
            ->post(route('agent_report.deduction.store'), $this->validPayload($agent, ['account' => 'Paid']))
            ->assertRedirect();

        $this->assertDatabaseHas('agent_deductions', [
            'agent_id' => $agent->id,
            'account'  => AgentDeduction::ACCOUNT_DEDUCTION,
        ]);
    }

    #[Test]
    public function amount_is_required_and_positive(): void
    {
        $agent = $this->makeAgent();

        $this->actingAs($this->user)
            ->post(route('agent_report.deduction.store'), $this->validPayload($agent, ['amount' => 0]))
            ->assertSessionHasErrors('amount');

        $this->actingAs($this->user)
            ->post(route('agent_report.deduction.store'), $this->validPayload($agent, ['amount' => -5]))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('agent_deductions', 0);
    }

    #[Test]
    public function agent_must_belong_to_the_same_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAgent = Agent::factory()->create(['agency_id' => $otherAgency->id]);

        $this->actingAs($this->user)
            ->post(route('agent_report.deduction.store'), $this->validPayload($otherAgent))
            ->assertNotFound(); // agency mismatch -> 404 (Receivable pattern)

        $this->assertDatabaseCount('agent_deductions', 0);
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
            ->post(route('agent_report.deduction.store'), $this->validPayload($agentA, ['applicant_id' => $applicant->id]))
            ->assertSessionHasErrors('applicant_id');

        $this->assertDatabaseCount('agent_deductions', 0);
    }

    // ---------- List / tab ----------

    #[Test]
    public function deductions_tab_lists_entries_and_filters_by_agent(): void
    {
        $agentA = $this->makeAgent();
        $agentB = $this->makeAgent();

        AgentDeduction::create($this->validPayload($agentA, ['particular' => 'Deduction for A']));
        AgentDeduction::create($this->validPayload($agentB, ['particular' => 'Deduction for B']));

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'deductions']))
            ->assertOk()
            ->assertSee('Deduction for A')
            ->assertSee('Deduction for B');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'deductions', 'agent_id' => $agentA->id]))
            ->assertOk()
            ->assertSee('Deduction for A')
            ->assertDontSee('Deduction for B');
    }

    // ---------- Agency isolation ----------

    #[Test]
    public function deductions_are_scoped_to_current_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAgent = Agent::factory()->create(['agency_id' => $otherAgency->id]);
        AgentDeduction::create($this->validPayload($otherAgent, [
            'agency_id' => $otherAgency->id,
            'particular' => 'Foreign deduction',
        ]));

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'deductions']))
            ->assertOk()
            ->assertDontSee('Foreign deduction');
    }
}
