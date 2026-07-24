<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantCvGenerateTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    // ── Full Body Photo ──

    #[Test]
    public function create_form_has_full_body_photo_upload_field(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.create'));

        $response->assertStatus(200);
        $response->assertSee('full_body_photo');
    }

    #[Test]
    public function edit_form_has_full_body_photo_upload_field(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.edit', $applicant));

        $response->assertStatus(200);
        $response->assertSee('full_body_photo');
    }

    #[Test]
    public function full_body_photo_can_be_uploaded_during_creation(): void
    {
        $file = UploadedFile::fake()->image('body.jpg', 800, 1200);

        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'      => 'Body',
                'last_name'       => 'Photo',
                'full_body_photo' => $file,
            ]);

        $response->assertSessionHasNoErrors();
        $applicant = Applicant::where('first_name', 'Body')->first();
        $this->assertNotNull($applicant->full_body_photo);
        $this->assertFileExists(storage_path('app/public/' . $applicant->full_body_photo));
    }

    #[Test]
    public function full_body_photo_is_resized(): void
    {
        $file = UploadedFile::fake()->image('body-large.jpg', 3000, 4000);

        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'      => 'Large',
                'last_name'       => 'Body',
                'full_body_photo' => $file,
            ]);

        $response->assertSessionHasNoErrors();
        $applicant = Applicant::where('first_name', 'Large')->first();
        $this->assertNotNull($applicant->full_body_photo);

        $savedPath = storage_path('app/public/' . $applicant->full_body_photo);
        [$width, $height] = getimagesize($savedPath);
        $this->assertLessThanOrEqual(1024, $width);
        $this->assertLessThanOrEqual(1024, $height);
    }

    #[Test]
    public function full_body_photo_requires_image(): void
    {
        $file = UploadedFile::fake()->create('doc.pdf', 100);

        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'      => 'Bad',
                'last_name'       => 'File',
                'full_body_photo' => $file,
            ]);

        $response->assertSessionHasErrors('full_body_photo');
    }

    // ── CV Generation ──

    #[Test]
    public function show_page_has_generate_cv_button(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant));

        $response->assertStatus(200);
        $response->assertSee('Generate CV');
    }

    #[Test]
    public function cv_can_be_generated_as_pdf_via_reports_route(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.resume', $applicant));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
