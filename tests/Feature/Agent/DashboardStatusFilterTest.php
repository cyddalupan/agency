<?php

namespace Tests\Feature\Agent;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\StatusCode;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardStatusFilterTest extends TestCase
{
    use RefreshDatabase;

    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an agency
        $agency = Agency::factory()->create();

        // Create the agent
        $this->agent = Agent::factory()->create([
            'agency_id' => $agency->id,
            'name'      => 'Test Agent',
            'email'     => 'agent@test.com',
        ]);

        // Seed status codes
        $this->seed(StatusCodesSeeder::class);

        $statusCodes = StatusCode::all();
        $this->assertGreaterThan(0, $statusCodes->count(), 'Status codes need to be seeded');

        // Create applicants with different statuses
        Applicant::factory()->count(5)->create([
            'agency_id'   => $agency->id,
            'agent_id'    => $this->agent->id,
            'status_code' => 0, // Pending
        ]);

        Applicant::factory()->count(3)->create([
            'agency_id'   => $agency->id,
            'agent_id'    => $this->agent->id,
            'status_code' => 1, // For Interview
        ]);

        Applicant::factory()->count(2)->create([
            'agency_id'   => $agency->id,
            'agent_id'    => $this->agent->id,
            'status_code' => 7, // For Deployment
        ]);

        Applicant::factory()->count(1)->create([
            'agency_id'   => $agency->id,
            'agent_id'    => $this->agent->id,
            'status_code' => 8, // Deployed
        ]);
    }

    #[Test]
    public function dashboard_loads_with_status_count_chips(): void
    {
        $response = $this->actingAs($this->agent, 'agent')
            ->get(route('agent.dashboard'));

        $response->assertStatus(200);

        // Should show status chips with counts
        $response->assertSeeHtml('>Pending<');
        $response->assertSeeHtml('>3<');
        $response->assertSeeHtml('>For Interview<');
        $response->assertSeeHtml('>For Deployment<');
        $response->assertSeeHtml('>Deployed<');
    }

    #[Test]
    public function status_chip_for_current_filter_is_active(): void
    {
        $response = $this->actingAs($this->agent, 'agent')
            ->get(route('agent.dashboard', ['status' => 1]));

        $response->assertStatus(200);
        $response->assertSeeHtml('>For Interview<');
    }

    #[Test]
    public function dashboard_with_status_filter_shows_only_matching_applicants(): void
    {
        $response = $this->actingAs($this->agent, 'agent')
            ->get(route('agent.dashboard', ['status' => 0]));

        $response->assertStatus(200);

        // Should see pending applicants
        $pending = Applicant::where('status_code', 0)
            ->where('agent_id', $this->agent->id)
            ->get();

        foreach ($pending as $p) {
            $response->assertSee($p->first_name);
        }
    }

    #[Test]
    public function dashboard_with_status_filter_excludes_other_statuses(): void
    {
        $response = $this->actingAs($this->agent, 'agent')
            ->get(route('agent.dashboard', ['status' => 1]));

        $response->assertStatus(200);

        // Pending applicants should NOT appear
        $pending = Applicant::where('status_code', 0)
            ->where('agent_id', $this->agent->id)
            ->get();

        foreach ($pending as $p) {
            $response->assertDontSee($p->first_name);
        }
    }

    #[Test]
    public function dashboard_shows_all_applicants_when_no_filter(): void
    {
        $response = $this->actingAs($this->agent, 'agent')
            ->get(route('agent.dashboard'));

        $response->assertStatus(200);

        // All 11 applicants should be visible
        $total = Applicant::where('agent_id', $this->agent->id)->count();
        $this->assertEquals(11, $total);
    }

    #[Test]
    public function status_filter_works_with_pagination(): void
    {
        // Create 25 more For Interview applicants to trigger pagination
        Applicant::factory()->count(25)->create([
            'agency_id'   => $this->agent->agency_id,
            'agent_id'    => $this->agent->id,
            'status_code' => 1,
        ]);

        $response = $this->actingAs($this->agent, 'agent')
            ->get(route('agent.dashboard', ['status' => 1]));

        $response->assertStatus(200);
        $response->assertSee('For Interview');
        $response->assertSee('Next');
    }

    #[Test]
    public function unauthenticated_users_cannot_access_dashboard(): void
    {
        $response = $this->get(route('agent.dashboard'));
        $response->assertRedirect(route('agent.login'));
    }

    #[Test]
    public function agent_sees_only_their_own_status_counts(): void
    {
        // Create another agent with different applicants
        $otherAgency = Agency::factory()->create();
        $otherAgent = Agent::factory()->create([
            'agency_id' => $otherAgency->id,
        ]);

        Applicant::factory()->count(10)->create([
            'agency_id'   => $otherAgency->id,
            'agent_id'    => $otherAgent->id,
            'status_code' => 1,
        ]);

        $response = $this->actingAs($this->agent, 'agent')
            ->get(route('agent.dashboard'));

        $response->assertStatus(200);
        // Should show this agent's counts, not the other
        $response->assertSeeHtml('>For Interview<');
        $response->assertSeeHtml('>3<');
    }
}
