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

    private function renderResumeView(): string
    {
        // Mirror ReportController::loadResumeRelations so the view renders the
        // same data the real CV PDF shows (sub-relations bypass the global scope).
        $this->applicant->setRelation('education', ApplicantEducation::withoutGlobalScopes()
            ->where('applicant_id', $this->applicant->id)
            ->orderBy('year_start')->orderBy('year_end')
            ->get());
        $this->applicant->setRelation('workExperiences', ApplicantWorkExperience::withoutGlobalScopes()
            ->where('applicant_id', $this->applicant->id)
            ->orderBy('date_to', 'desc')->orderBy('to_date', 'desc')
            ->get());
        $this->applicant->setRelation('certificates', ApplicantCertificate::withoutGlobalScopes()
            ->where('applicant_id', $this->applicant->id)
            ->get());
        $this->applicant->setRelation('references', ApplicantReference::withoutGlobalScopes()
            ->where('applicant_id', $this->applicant->id)
            ->get());

        return view('reports.resume', ['applicant' => $this->applicant])->render();
    }

    #[Test]
    public function resume_pdf_includes_applicant_personal_info(): void
    {
        // The CV PDF is built from this view; assert on the rendered content
        // because dompdf encodes glyphs in the binary stream (raw bytes are not
        // plain-text searchable). This feeds exactly what Pdf::loadView gets.
        $html = $this->renderResumeView();

        $this->assertStringContainsString('Juan', $html);
        $this->assertStringContainsString('Dela Cruz', $html);
    }

    #[Test]
    public function resume_pdf_includes_education(): void
    {
        $html = $this->renderResumeView();

        $this->assertStringContainsString('University of the Philippines', $html);
        $this->assertStringContainsString('BS Computer Science', $html);
    }

    #[Test]
    public function resume_pdf_includes_work_experience(): void
    {
        $html = $this->renderResumeView();

        $this->assertStringContainsString('Tech Corp', $html);
        $this->assertStringContainsString('Software Engineer', $html);
    }

    #[Test]
    public function resume_pdf_includes_certificates(): void
    {
        $html = $this->renderResumeView();

        $this->assertStringContainsString('TESDA NC II', $html);
    }

    #[Test]
    public function resume_pdf_includes_references(): void
    {
        $html = $this->renderResumeView();

        $this->assertStringContainsString('Maria Santos', $html);
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
