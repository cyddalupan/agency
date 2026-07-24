<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use App\Models\ApplicantEducation;
use App\Models\ApplicantPassport;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantShowSubTablesTest extends TestCase
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

        $this->applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => 'with',
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    #[Test]
    public function show_page_loads_with_no_sub_table_data(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant));

        $response->assertOk();
        $response->assertSee($this->applicant->full_name);
        // Should not crash with UnexpectedValueException when sub-tables are empty
        $response->assertSee('No records yet');
    }

    #[Test]
    public function show_page_displays_existing_passport(): void
    {
        ApplicantPassport::create([
            'agency_id' => $this->agency->id,
            'applicant_id' => $this->applicant->id,
            'passport_no' => 'P12345678',
            'issue_date' => '2024-01-15',
            'expiry_date' => '2029-01-15',
            'place_of_issue' => 'DFA Manila',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant));

        $response->assertOk();
        $response->assertSee('P12345678');
        $response->assertSee('DFA Manila');
    }

    #[Test]
    public function show_page_displays_existing_education(): void
    {
        ApplicantEducation::create([
            'agency_id' => $this->agency->id,
            'applicant_id' => $this->applicant->id,
            'level' => 'college',
            'school' => 'University of the Philippines',
            'degree' => 'BS Nursing',
            'year_graduated' => '2020',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant));

        $response->assertOk();
        $response->assertSee('University of the Philippines');
        $response->assertSee('BS Nursing');
    }

    #[Test]
    public function show_page_displays_multiple_sub_table_types(): void
    {
        // Add passport
        ApplicantPassport::create([
            'agency_id' => $this->agency->id,
            'applicant_id' => $this->applicant->id,
            'passport_no' => 'P12345678',
        ]);

        // Add education
        ApplicantEducation::create([
            'agency_id' => $this->agency->id,
            'applicant_id' => $this->applicant->id,
            'level' => 'college',
            'school' => 'FEU',
            'degree' => 'BS Nursing',
        ]);

        // Add skill
        $skill = new \App\Models\ApplicantSkill;
        $skill->agency_id = $this->agency->id;
        $skill->applicant_id = $this->applicant->id;
        $skill->skill_name = 'Caregiving';
        $skill->proficiency = 'expert';
        $skill->save();

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant));

        $response->assertOk();
        $response->assertSee('P12345678');
        $response->assertSee('FEU');
        $response->assertSee('Caregiving');
    }
}
