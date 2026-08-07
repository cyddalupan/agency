<?php

namespace Tests\Feature\Report;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Reports -> Agents.
 *
 * Agency users must ONLY see agents belonging to their own agency. Super admins
 * may see all agencies. Regression: the agents report previously queried every
 * agent with no agency scope, leaking agents from other agencies (e.g. a Gulf
 * user seeing Finas agents).
 */
class ReportAgentReportTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->user = User::factory()->create(['agency_id' => $this->agency->id]);
    }

    #[Test]
    public function guest_cannot_access_agents_report(): void
    {
        $this->get(route('reports.agents'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function agency_user_only_sees_agents_from_their_own_agency(): void
    {
        $otherAgency = Agency::factory()->create();

        $mine   = Agent::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Gulf Agent One']);
        $theirs = Agent::factory()->create(['agency_id' => $otherAgency->id, 'name' => 'Finas Agent Leak']);

        $response = $this->actingAs($this->user)
            ->get(route('reports.agents'))
            ->assertOk();

        $html = $response->getContent();
        // Own agency agent present.
        $this->assertStringContainsString($mine->name, $html);
        // Other agency's agent must NOT appear.
        $this->assertStringNotContainsString($theirs->name, $html);
    }

    #[Test]
    public function agency_user_agents_export_excludes_other_agencies(): void
    {
        $otherAgency = Agency::factory()->create();

        $mine   = Agent::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Export Mine']);
        $theirs = Agent::factory()->create(['agency_id' => $otherAgency->id, 'name' => 'Export Leak']);

        $response = $this->actingAs($this->user)
            ->get(route('reports.agents.export'))
            ->assertOk();

        $csv = $response->streamedContent();
        $this->assertStringContainsString($mine->name, $csv);
        $this->assertStringNotContainsString($theirs->name, $csv);
    }

    #[Test]
    public function super_admin_sees_agents_from_all_agencies(): void
    {
        $super = User::factory()->create(['user_type' => 'super_admin', 'agency_id' => null]);
        $otherAgency = Agency::factory()->create();

        $mine   = Agent::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Super Mine']);
        $theirs = Agent::factory()->create(['agency_id' => $otherAgency->id, 'name' => 'Super Other']);

        $html = $this->actingAs($super)
            ->get(route('reports.agents'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString($mine->name, $html);
        $this->assertStringContainsString($theirs->name, $html);
    }
}
