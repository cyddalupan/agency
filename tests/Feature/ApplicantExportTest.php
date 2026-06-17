<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantExportTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Agency $otherAgency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->otherAgency = Agency::factory()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->user = User::factory()->create(['agency_id' => $this->agency->id]);
    }

    #[Test]
    public function guest_cannot_export(): void
    {
        $this->get(route('applicants.export'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function export_returns_csv_file(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=applicants.csv');
    }

    #[Test]
    public function export_includes_csv_headers(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $response->assertSeeTextInOrder([
            'First Name',
            'Last Name',
            'Email',
            'Contact',
            'Status',
        ]);
    }

    #[Test]
    public function export_includes_applicant_data(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria@example.com',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $response->assertSeeText('Maria');
        $response->assertSeeText('Santos');
        $response->assertSeeText('maria@example.com');
    }

    #[Test]
    public function export_is_scoped_to_tenant_agency(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Tenant',
            'last_name' => 'User',
        ]);
        Applicant::factory()->create([
            'agency_id' => $this->otherAgency->id,
            'first_name' => 'Other',
            'last_name' => 'Agency',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $response->assertSeeText('Tenant');
        $response->assertSeeText('User');
        $response->assertDontSeeText('Other');
    }
}
