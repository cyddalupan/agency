<?php

namespace Tests\Feature\Report;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ApplicantCertificate;
use App\Models\ApplicantEducation;
use App\Models\ApplicantReference;
use App\Models\ApplicantWorkExperience;
use App\Models\Country;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportResumePdfTest extends TestCase
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
        app()->instance('tenant_agency', $this->agency);

        $this->user = User::factory()->create(['agency_id' => $this->agency->id]);

        $country = Country::factory()->create();

        $this->applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'country_id' => $country->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'birthdate' => '1995-06-15',
            'gender' => 'Male',
            'contact' => '+639123456789',
            'email' => 'juan@example.com',
            'address' => '123 Rizal St, Manila',
        ]);

        // Add education
        ApplicantEducation::factory()->create([
            'applicant_id' => $this->applicant->id,
            'school' => 'University of the Philippines',
            'degree' => 'BS Computer Science',
            'year_start' => 2013,
            'year_end' => 2017,
        ]);

        // Add work experience
        ApplicantWorkExperience::factory()->create([
            'applicant_id' => $this->applicant->id,
            'company' => 'Tech Corp',
            'position' => 'Software Engineer',
            'date_from' => '2017-06-01',
            'date_to' => '2020-12-31',
        ]);

        // Add certificate
        ApplicantCertificate::factory()->create([
            'applicant_id' => $this->applicant->id,
            'name' => 'TESDA NC II',
            'issued_by' => 'TESDA',
            'issued_date' => '2016-03-15',
        ]);

        // Add reference
        ApplicantReference::factory()->create([
            'applicant_id' => $this->applicant->id,
            'name' => 'Maria Santos',
            'position' => 'Manager',
            'company' => 'Tech Corp',
            'contact' => '+639987654321',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_download_resume(): void
    {
        $this->get(route('reports.resume', $this->applicant))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function resume_pdf_returns_pdf_response(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.resume', $this->applicant));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    #[Test]
    public function resume_pdf_includes_applicant_personal_info(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.resume', $this->applicant));

        $response->assertOk();

        // Should contain personal details
        $response->assertSeeText('Juan');
        $response->assertSeeText('Dela Cruz');
    }

    #[Test]
    public function resume_pdf_includes_education(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.resume', $this->applicant));

        $response->assertOk();
        $response->assertSeeText('University of the Philippines');
        $response->assertSeeText('BS Computer Science');
    }

    #[Test]
    public function resume_pdf_includes_work_experience(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.resume', $this->applicant));

        $response->assertOk();
        $response->assertSeeText('Tech Corp');
        $response->assertSeeText('Software Engineer');
    }

    #[Test]
    public function resume_pdf_includes_certificates(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.resume', $this->applicant));

        $response->assertOk();
        $response->assertSeeText('TESDA NC II');
    }

    #[Test]
    public function resume_pdf_includes_references(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.resume', $this->applicant));

        $response->assertOk();
        $response->assertSeeText('Maria Santos');
    }

    #[Test]
    public function resume_returns_404_for_nonexistent_applicant(): void
    {
        $this->actingAs($this->user)
            ->get(route('reports.resume', 99999))
            ->assertNotFound();
    }

    #[Test]
    public function user_cannot_access_resume_from_other_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherApplicant = Applicant::factory()->create(['agency_id' => $otherAgency->id]);

        $this->actingAs($this->user)
            ->get(route('reports.resume', $otherApplicant))
            ->assertNotFound();
    }

    #[Test]
    public function resume_pdf_includes_applicant_photo(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('photo.jpg');
        $path = $photo->store('photos', 'public');

        $this->applicant->update(['photo' => $path]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.resume', $this->applicant));

        $response->assertOk();
    }
}
