<?php

namespace Tests\Feature\Applicant;

use App\Models\Applicant;
use App\Models\Agency;
use App\Models\StatusCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantCsvExportTest extends TestCase
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
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_export_csv(): void
    {
        $response = $this->get(route('applicants.export'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function export_returns_csv_with_correct_headers(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=applicants.csv');
    }

    #[Test]
    public function export_contains_expected_column_headers(): void
    {
        app()->instance('tenant_agency', $this->agency);

        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

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
    public function export_contains_applicant_data(): void
    {
        app()->instance('tenant_agency', $this->agency);

        $applicant = Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'email'      => 'juan@example.com',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Juan', $content);
        $this->assertStringContainsString('Dela Cruz', $content);
        $this->assertStringContainsString('juan@example.com', $content);
    }

    #[Test]
    public function export_is_tenant_scoped(): void
    {
        app()->instance('tenant_agency', $this->agency);

        Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'first_name' => 'Our Applicant',
        ]);

        $otherAgency = Agency::factory()->create();
        Applicant::factory()->create([
            'agency_id'  => $otherAgency->id,
            'first_name' => 'Other Agency Applicant',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Our Applicant', $content);
        $this->assertStringNotContainsString('Other Agency Applicant', $content);
    }

    #[Test]
    public function export_handles_no_applicants_gracefully(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('First Name', $content);
        $this->assertStringContainsString('Last Name', $content);
        // Should have header row but no data rows
        $lines = array_filter(explode("\n", $content));
        $this->assertCount(1, $lines);
    }
}
