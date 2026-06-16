<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use App\Models\Country;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantReportTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Country $countryPH;
    private Country $countrySA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->user = User::factory()->create(['agency_id' => $this->agency->id]);

        $this->countryPH = Country::factory()->create(['name' => 'Philippines']);
        $this->countrySA = Country::factory()->create(['name' => 'Saudi Arabia']);
    }

    #[Test]
    public function guest_cannot_access_reports(): void
    {
        $response = $this->get(route('reports.applicants'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function report_page_loads_with_filters(): void
    {
        Applicant::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
            'country_id' => $this->countryPH->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.applicants'));

        $response->assertOk();
        $response->assertViewIs('reports.applicants');
        $response->assertViewHas('applicants');
        $response->assertViewHas('statusCodes');
        $response->assertViewHas('countries');
    }

    #[Test]
    public function filters_by_status_code(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'status_code' => 0,
            'country_id' => $this->countryPH->id,
        ]);
        Applicant::factory()->count(2)->create([
            'agency_id' => $this->agency->id,
            'status_code' => 1,
            'country_id' => $this->countryPH->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.applicants', ['status_code' => 1]));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('applicants'));
    }

    #[Test]
    public function filters_by_country(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'country_id' => $this->countryPH->id,
        ]);
        Applicant::factory()->count(2)->create([
            'agency_id' => $this->agency->id,
            'country_id' => $this->countrySA->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.applicants', ['country_id' => $this->countrySA->id]));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('applicants'));
    }

    #[Test]
    public function filters_by_date_range(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'country_id' => $this->countryPH->id,
            'created_at' => '2026-01-01',
        ]);
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'country_id' => $this->countryPH->id,
            'created_at' => '2026-06-01',
        ]);
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'country_id' => $this->countryPH->id,
            'created_at' => '2026-06-15',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.applicants', [
                'date_from' => '2026-06-01',
                'date_to' => '2026-06-10',
            ]));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('applicants'));
    }

    #[Test]
    public function combines_multiple_filters(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'status_code' => 2,
            'country_id' => $this->countrySA->id,
            'created_at' => '2026-05-15',
        ]);
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'status_code' => 0,
            'country_id' => $this->countrySA->id,
            'created_at' => '2026-05-15',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.applicants', [
                'status_code' => 2,
                'country_id' => $this->countrySA->id,
                'date_from' => '2026-05-01',
                'date_to' => '2026-05-31',
            ]));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('applicants'));
    }

    #[Test]
    public function is_scoped_to_tenant_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        Applicant::factory()->count(3)->create([
            'agency_id' => $otherAgency->id,
            'country_id' => $this->countryPH->id,
        ]);
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'country_id' => $this->countryPH->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.applicants'));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('applicants'));
    }

    #[Test]
    public function renders_csv_export_link(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.applicants'));

        $response->assertOk();
        $response->assertSee('Export');
    }
}
