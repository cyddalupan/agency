<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\Applicant;
use App\Models\ApplicantCertificate;
use App\Models\ApplicantEducation;
use App\Models\ApplicantPassport;
use App\Models\ApplicantRequirement;
use App\Models\ApplicantWorkExperience;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PortalProfileSubEntitiesTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);
    }

    // === Education Section ===

    #[Test]
    public function profile_shows_education_section(): void
    {
        ApplicantEducation::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicant->id,
            'level'        => 'College',
            'school'       => 'University of the Philippines',
            'course'       => 'BS Computer Science',
            'year_graduated' => '2020',
        ]);

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Education');
        $response->assertSee('University of the Philippines');
        $response->assertSee('BS Computer Science');
    }

    #[Test]
    public function profile_shows_multiple_education_entries(): void
    {
        ApplicantEducation::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicant->id,
            'level'        => 'College',
            'school'       => 'University A',
            'course'       => 'BS IT',
            'year_graduated' => '2020',
        ]);

        ApplicantEducation::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicant->id,
            'level'        => 'High School',
            'school'       => 'School B',
            'course'       => null,
            'year_graduated' => '2016',
        ]);

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('University A');
        $response->assertSee('School B');
    }

    #[Test]
    public function profile_shows_empty_education_state(): void
    {
        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Education');
    }

    // === Passport Section ===

    #[Test]
    public function profile_shows_passport_section(): void
    {
        ApplicantPassport::create([
            'agency_id'       => $this->agency->id,
            'applicant_id'    => $this->applicant->id,
            'passport_number' => 'P12345678',
            'issue_date'      => '2020-01-15',
            'expiry_date'     => '2030-01-15',
            'place_issue'     => 'Manila',
        ]);

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Passport');
        $response->assertSee('P12345678');
    }

    #[Test]
    public function profile_shows_empty_passport_state(): void
    {
        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Passport');
    }

    // === Certificates Section ===

    #[Test]
    public function profile_shows_certificates_section(): void
    {
        ApplicantCertificate::create([
            'agency_id'       => $this->agency->id,
            'applicant_id'    => $this->applicant->id,
            'certificate_name' => 'TESDA NCII',
            'institution'     => 'TESDA',
            'date_obtained'   => '2021-06-01',
        ]);

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Certificates');
        $response->assertSee('TESDA NCII');
    }

    #[Test]
    public function profile_shows_multiple_certificates(): void
    {
        ApplicantCertificate::create([
            'agency_id'       => $this->agency->id,
            'applicant_id'    => $this->applicant->id,
            'certificate_name' => 'TESDA NCII',
            'institution'     => 'TESDA',
            'date_obtained'   => '2021-06-01',
        ]);

        ApplicantCertificate::create([
            'agency_id'       => $this->agency->id,
            'applicant_id'    => $this->applicant->id,
            'certificate_name' => 'OJT Certificate',
            'institution'     => 'Company X',
            'date_obtained'   => '2020-03-15',
        ]);

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('TESDA NCII');
        $response->assertSee('OJT Certificate');
    }

    #[Test]
    public function profile_shows_empty_certificates_state(): void
    {
        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Certificates');
    }

    // === Work Experience Section ===

    #[Test]
    public function profile_shows_work_experience_section(): void
    {
        ApplicantWorkExperience::create([
            'agency_id'       => $this->agency->id,
            'applicant_id'    => $this->applicant->id,
            'company'         => 'Tech Corp',
            'position'        => 'Software Developer',
            'start_date'      => '2019-01-01',
            'end_date'        => '2022-12-31',
        ]);

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Work Experience');
        $response->assertSee('Tech Corp');
        $response->assertSee('Software Developer');
    }

    #[Test]
    public function profile_shows_multiple_work_experiences(): void
    {
        ApplicantWorkExperience::create([
            'agency_id'       => $this->agency->id,
            'applicant_id'    => $this->applicant->id,
            'company'         => 'Company A',
            'position'        => 'Junior Dev',
            'start_date'      => '2018-01-01',
            'end_date'        => '2020-12-31',
        ]);

        ApplicantWorkExperience::create([
            'agency_id'       => $this->agency->id,
            'applicant_id'    => $this->applicant->id,
            'company'         => 'Company B',
            'position'        => 'Senior Dev',
            'start_date'      => '2021-01-01',
            'end_date'        => null,
        ]);

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Company A');
        $response->assertSee('Company B');
    }

    #[Test]
    public function profile_shows_empty_work_experience_state(): void
    {
        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Work Experience');
    }

    // === Requirements Section ===

    #[Test]
    public function profile_shows_requirements_section(): void
    {
        ApplicantRequirement::create([
            'agency_id'       => $this->agency->id,
            'applicant_id'    => $this->applicant->id,
            'type'            => 'NBI Clearance',
            'status'          => 'submitted',
        ]);

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Requirements');
        $response->assertSee('NBI Clearance');
    }

    #[Test]
    public function profile_shows_multiple_requirements(): void
    {
        ApplicantRequirement::create([
            'agency_id'       => $this->agency->id,
            'applicant_id'    => $this->applicant->id,
            'type'            => 'NBI Clearance',
            'status'          => 'submitted',
        ]);

        ApplicantRequirement::create([
            'agency_id'       => $this->agency->id,
            'applicant_id'    => $this->applicant->id,
            'type'            => 'Medical Exam',
            'status'          => 'pending',
        ]);

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('NBI Clearance');
        $response->assertSee('Medical Exam');
    }

    #[Test]
    public function profile_shows_empty_requirements_state(): void
    {
        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Requirements');
    }
}
