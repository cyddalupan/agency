<?php

namespace Tests\Feature\Admin;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\StatusCode;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardPipelineTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();

        $this->user = User::factory()->create([
            'user_type' => 'admin',
            'agency_id' => $this->agency->id,
        ]);

        $this->seed(StatusCodesSeeder::class);

        Applicant::factory()->count(5)->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 0,
        ]);
        Applicant::factory()->count(3)->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 1,
        ]);
        Applicant::factory()->count(2)->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 7,
        ]);
        Applicant::factory()->count(1)->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 8,
        ]);
    }

    #[Test]
    public function agency_dashboard_shows_deployment_pipeline_with_counts(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('agency.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Deployment Pipeline');
        $response->assertSee('Pending');
        $response->assertSee('For Interview');
    }

    #[Test]
    public function pipeline_chips_have_counts(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('agency.dashboard'));

        $response->assertStatus(200);
        // With 5 pending applicants
        $response->assertSeeInOrder(['Pending', '5']);
    }

    #[Test]
    public function pipeline_chips_link_to_filtered_dashboard(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('agency.dashboard'));

        $response->assertStatus(200);
        // Chips should link to the dashboard with status param
        $response->assertSee('href=');
        $response->assertSee('status=');
    }

    #[Test]
    public function pipeline_only_shows_statuses_with_applicants(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('agency.dashboard'));

        $response->assertStatus(200);
        // Deployed (code 8) has 1 applicant, should show
        $response->assertSee('Deployed');
    }

    #[Test]
    public function clicking_pipeline_chip_filters_recent_applicants(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('agency.dashboard', ['status' => 0]));

        $response->assertStatus(200);
        // Should see pending applicant names
        $pending = Applicant::where('status_code', 0)->get();
        foreach ($pending as $p) {
            $response->assertSee($p->first_name);
        }
    }

    #[Test]
    public function recent_applicants_respects_status_filter(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('agency.dashboard', ['status' => 1]));

        $response->assertStatus(200);
        // Should NOT show pending applicants
        $pending = Applicant::where('status_code', 0)->get();
        foreach ($pending as $p) {
            $response->assertDontSee($p->first_name);
        }
    }
}
