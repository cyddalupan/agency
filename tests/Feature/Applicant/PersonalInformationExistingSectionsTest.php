<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS "Personal Information" — PI:1 Basic Information tab: pre-wired sections (TDD).
 *
 * Address + Educational Background, Work Experience, Skills, References and
 * Salary Record are all surfaced on the default Basic Information tab.
 */
class PersonalInformationExistingSectionsTest extends TestCase
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
            'address'      => '123 Rizal St, Manila',
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    #[Test]
    public function basic_tab_renders_address(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Address', $html);
        $this->assertStringContainsString('123 Rizal St, Manila', $html);
    }

    #[Test]
    public function basic_tab_renders_all_education_work_skills_reference_salary_sections(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        foreach ([
            'Education', 'Work Experience', 'Skills', 'References', 'Salary Records',
        ] as $section) {
            $this->assertStringContainsString($section, $html, "Section '{$section}' should render in the Basic tab");
        }
    }
}
