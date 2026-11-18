<?php

namespace Tests\Feature\Report;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportStatisticsTest extends TestCase
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

        // Model the real path: `reports.statistics` is one of the screens only
        // a privileged account opens (reports/index.blade.php gates the whole
        // Statistics card on isPrivileged(), which matches the shipped
        // MAE,EVELYN,ANGEL name list). An "admin" fixture was never enough.
        $this->user = User::factory()->privileged()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function guest_cannot_access_statistics(): void
    {
        $this->get(route('reports.statistics'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function statistics_page_loads_successfully(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.statistics'));

        $response->assertOk();
        $response->assertViewIs('reports.statistics');
    }

    #[Test]
    public function statistics_shows_total_applicants_count(): void
    {
        Applicant::factory()->count(5)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.statistics'));

        $response->assertOk();
        $response->assertViewHas('totalApplicants');
        $this->assertEquals(5, $response->viewData('totalApplicants'));
    }

    #[Test]
    public function statistics_shows_applicants_by_status(): void
    {
        // Create applicants with different statuses
        Applicant::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
            'status_code' => 0, // For processing
        ]);
        Applicant::factory()->count(2)->create([
            'agency_id' => $this->agency->id,
            'status_code' => 1, // Deployed
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.statistics'));

        $response->assertOk();
        $response->assertViewHas('applicantsByStatus');
        $this->assertCount(2, $response->viewData('applicantsByStatus'));
    }

    #[Test]
    public function statistics_is_scoped_to_tenant_agency(): void
    {
        $otherAgency = Agency::factory()->create();

        Applicant::factory()->count(10)->create([
            'agency_id' => $otherAgency->id,
        ]);
        Applicant::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.statistics'));

        $response->assertOk();
        $this->assertEquals(3, $response->viewData('totalApplicants'));
    }

    #[Test]
    public function statistics_shows_top_destinations(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.statistics'));

        $response->assertOk();
        $response->assertViewHas('topDestinations');
    }

    #[Test]
    public function statistics_shows_monthly_deployment_trends(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.statistics'));

        $response->assertOk();
        $response->assertViewHas('monthlyDeployments');
    }
}
