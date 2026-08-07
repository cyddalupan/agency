<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS PI: 7. Generate CV — the generated CV (reports.resume view + controller)
 * must follow the required display format:
 *   Name, Contact#, Gender, Age, Branch, Agent, Position, Country, FRA, Status,
 *   Firstimer/Ex-Abroad, Encoder
 * and must include a STATUS field.
 */
class ResumeGenerateTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Atty. Maricon Ramos',
        ]);

        $position = \App\Models\Position::factory()->create(['name' => 'Domestic Helper']);
        $country = \App\Models\Country::factory()->create(['name' => 'Saudi Arabia']);
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);

        $this->applicant = Applicant::factory()->create([
            'agency_id'     => $this->agency->id,
            'first_name'    => 'Juan',
            'middle_name'   => 'Santos',
            'last_name'     => 'Dela Cruz',
            'birthdate'     => '1990-05-14',
            'gender'        => 'Male',
            'contact'       => '09171234567',
            'branch_id'     => $branch->id,
            'agent_id'      => $agent->id,
            'position_id'   => $position->id,
            'country_id'    => $country->id,
            'status_code'   => 5, // For Selected
            'fra'           => 'for_fra',
            'firstimer_type'=> 'firstimer',
            'encoder'       => 'finas Admin - Aug 04, 2026 08:00 AM',
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    private function renderResume(): string
    {
        // Mirror the controller: load resume sub-relations so the view renders cleanly.
        \App\Models\ApplicantEducation::factory()->create(['applicant_id' => $this->applicant->id]);
        \App\Models\ApplicantWorkExperience::factory()->create(['applicant_id' => $this->applicant->id]);
        \App\Models\ApplicantCertificate::factory()->create(['applicant_id' => $this->applicant->id]);
        \App\Models\ApplicantReference::factory()->create(['applicant_id' => $this->applicant->id]);

        $this->applicant->load(['position', 'country', 'agent', 'statusCode', 'skills']);

        return view('reports.resume', ['applicant' => $this->applicant])->render();
    }

    #[Test]
    public function resume_endpoint_returns_pdf(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.resume', $this->applicant));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('resume-'.$this->applicant->id.'.pdf', $response->headers->get('Content-Disposition'));
    }

    #[Test]
    public function resume_includes_status_and_all_required_format_fields(): void
    {
        $html = $this->renderResume();

        // Name
        $this->assertStringContainsString('DELA CRUZ', $html);
        $this->assertStringContainsString('Juan', $html);
        // Contact#
        $this->assertStringContainsString('09171234567', $html);
        // Gender
        $this->assertStringContainsString('Male', $html);
        // Age (not just birthdate)
        $this->assertStringContainsString('Age', $html);
        $this->assertStringContainsString('36', $html); // born 1990 -> 36 (2026)
        // Branch
        $this->assertStringContainsString('Manila', $html);
        // Agent
        $this->assertStringContainsString('Atty. Maricon Ramos', $html);
        // Position (shown in subtitle)
        $this->assertStringContainsString('Domestic Helper', $html);
        // Country
        $this->assertStringContainsString('Saudi Arabia', $html);
        // FRA
        $this->assertStringContainsString('FRA', $html);
        $this->assertStringContainsString('For FRA', $html);
        // Status (PI:7 item 1)
        $this->assertStringContainsString('Status', $html);
        $this->assertStringContainsString('For Selected', $html);
        // Firstimer / Ex-Abroad
        $this->assertStringContainsString('Firstimer', $html);
        // Encoder
        $this->assertStringContainsString('finas Admin - Aug 04, 2026', $html);
    }
}
