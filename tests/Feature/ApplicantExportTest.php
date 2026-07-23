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
        app()->instance('tenant_agency', $this->agency);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('First Name', $content);
        $this->assertStringContainsString('Last Name', $content);
        $this->assertStringContainsString('Email', $content);
        $this->assertStringContainsString('Contact', $content);
        $this->assertStringContainsString('Status', $content);
    }

    #[Test]
    public function export_includes_applicant_data(): void
    {
        app()->instance('tenant_agency', $this->agency);

        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria@example.com',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('Maria', $content);
        $this->assertStringContainsString('Santos', $content);
        $this->assertStringContainsString('maria@example.com', $content);
    }

    #[Test]
    public function export_is_scoped_to_tenant_agency(): void
    {
        app()->instance('tenant_agency', $this->agency);

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
        $content = $response->streamedContent();
        $this->assertStringContainsString('Tenant', $content);
        $this->assertStringContainsString('User', $content);
        $this->assertStringNotContainsString('Other', $content);
    }
}
