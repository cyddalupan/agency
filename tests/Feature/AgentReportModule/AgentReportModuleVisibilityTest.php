<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-10-23 ("Agents Report Module"):
 *  - Branch accounts see only the agents registered under their branch;
 *  - Main Office accounts see all agents.
 *
 * Cyd 2026-11-09 ("GULF: Agent Reports module"): the module is Gulf-only and
 * is limited to Admin, Accounting and Branch users. The fixture agency is
 * therefore the Gulf tenant (subdomain "gulf"); the Gulf-only and staff-denied
 * cases are covered in AgentReportAccessScopeTest.
 */
class AgentReportModuleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Branch $mainOffice;
    private Branch $iloilo;
    private Agent $mainAgent;
    private Agent $iloiloAgent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->mainOffice = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Main Office']);
        $this->iloilo = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'ILOILO']);

        $this->mainAgent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->mainOffice->id,
            'name' => 'MAIN OFFICE AGENT',
        ]);
        $this->iloiloAgent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->iloilo->id,
            'name' => 'ILOILO AGENT',
        ]);
        Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => null,
            'name' => 'UNASSIGNED AGENT',
        ]);
    }

    private function user(string $type, Branch $branch, string $name): User
    {
        return User::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $branch->id,
            'user_type' => $type,
            'name'      => $name,
        ]);
    }

    #[Test]
    public function branch_users_can_open_the_agents_report_module(): void
    {
        $branchUser = $this->user('branch', $this->iloilo, 'ILOILO BRANCH');

        $this->assertTrue($branchUser->canAccessModule('agent_report'));

        $this->actingAs($branchUser)
            ->get(route('agent_report.index'))
            ->assertOk()
            ->assertSee('Agents Report');
    }

    #[Test]
    public function branch_users_only_see_agents_of_their_own_branch(): void
    {
        $branchUser = $this->user('branch', $this->iloilo, 'ILOILO BRANCH');

        $response = $this->actingAs($branchUser)->get(route('agent_report.index'));

        $response->assertOk()
            ->assertSee('ILOILO AGENT')
            ->assertDontSee('MAIN OFFICE AGENT')
            ->assertDontSee('UNASSIGNED AGENT');
    }

    #[Test]
    public function main_office_users_see_every_agent(): void
    {
        $mainUser = $this->user('admin', $this->mainOffice, 'MAIN ADMIN');

        $response = $this->actingAs($mainUser)->get(route('agent_report.index'));

        $response->assertOk()
            ->assertSee('MAIN OFFICE AGENT')
            ->assertSee('ILOILO AGENT')
            ->assertSee('UNASSIGNED AGENT');
    }

    #[Test]
    public function branch_users_cannot_open_another_branchs_deduction(): void
    {
        $branchUser = $this->user('branch', $this->iloilo, 'ILOILO BRANCH');

        $deduction = \App\Models\AgentDeduction::create([
            'agency_id' => $this->agency->id,
            'user_id'   => $branchUser->id,
            'agent_id'  => $this->mainAgent->id,
            'date'      => now()->toDateString(),
            'account'   => \App\Models\AgentDeduction::ACCOUNT_DEDUCTION,
            'amount'    => 100.00,
            'particular'=> 'Other branch row',
        ]);

        $this->actingAs($branchUser)
            ->get(route('agent_report.deduction.show', $deduction))
            ->assertNotFound();
    }
}
