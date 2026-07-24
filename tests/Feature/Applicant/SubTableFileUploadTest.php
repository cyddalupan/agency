<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubTableFileUploadTest extends TestCase
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

    // ── Passport File Uploads ─────────────────────────────────────

    #[Test]
    public function passport_can_upload_a_jpg_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('passport.jpg');
        $response = $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'passport']), [
                'passport_no'    => 'P12345678',
                'issue_date'     => '2024-01-15',
                'expiry_date'    => '2029-01-15',
                'place_of_issue' => 'DFA Manila',
                'file'           => $file,
            ]);

        $response->assertSessionHas('success');
        $this->applicant->refresh();
        $this->assertNotNull($this->applicant->passport);
        $this->assertNotNull($this->applicant->passport->file_path);
        Storage::disk('public')->assertExists($this->applicant->passport->file_path);
    }

    #[Test]
    public function passport_can_upload_a_pdf_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('passport.pdf', 100, 'application/pdf');
        $response = $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'passport']), [
                'passport_no'    => 'P87654321',
                'issue_date'     => '2024-06-01',
                'expiry_date'    => '2029-06-01',
                'place_of_issue' => 'DFA Cebu',
                'file'           => $file,
            ]);

        $response->assertSessionHas('success');
        $this->applicant->refresh();
        $this->assertNotNull($this->applicant->passport->file_path);
        Storage::disk('public')->assertExists($this->applicant->passport->file_path);
    }

    #[Test]
    public function passport_replaces_old_file_on_new_upload(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old_passport.jpg');
        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'passport']), [
                'passport_no' => 'P12345678',
                'file'        => $oldFile,
            ]);

        $oldPath = $this->applicant->fresh()->passport->file_path;

        $newFile = UploadedFile::fake()->image('new_passport.jpg');
        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'passport']), [
                'passport_no' => 'P87654321',
                'file'        => $newFile,
            ]);

        $newPath = $this->applicant->fresh()->passport->file_path;
        $this->assertNotEquals($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    // ── Certificates File Uploads ─────────────────────────────────

    #[Test]
    public function certificate_can_upload_a_pdf_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $response = $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'certificates']), [
                'type'           => 'training',
                'certificate_no' => 'CERT001',
                'file'           => $file,
            ]);

        $response->assertSessionHas('success');
        $cert = $this->applicant->certificates()->first();
        $this->assertNotNull($cert->file_path);
        Storage::disk('public')->assertExists($cert->file_path);
    }

    // ── Requirements File Uploads ─────────────────────────────────

    #[Test]
    public function requirement_can_upload_a_jpg_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('requirement.jpg');
        $response = $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'requirements']), [
                'type'           => 'nbi_clearance',
                'reference_no'   => 'NBI001',
                'file'           => $file,
            ]);

        $response->assertSessionHas('success');
        $req = $this->applicant->requirements()->first();
        $this->assertNotNull($req->file_path);
        Storage::disk('public')->assertExists($req->file_path);
    }

    #[Test]
    public function requirement_can_upload_a_pdf_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('requirement.pdf', 100, 'application/pdf');
        $response = $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'requirements']), [
                'type'           => 'nbi_clearance',
                'reference_no'   => 'NBI002',
                'file'           => $file,
            ]);

        $response->assertSessionHas('success');
        $req = $this->applicant->requirements()->first();
        $this->assertNotNull($req->file_path);
        Storage::disk('public')->assertExists($req->file_path);
    }

    // ── Passport Show Page Displays File ──────────────────────────

    #[Test]
    public function passport_file_displays_as_thumbnail_on_show_page(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('passport_photo.jpg');
        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'passport']), [
                'passport_no' => 'P12345678',
                'file'        => $file,
            ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant));

        $response->assertOk();
        $filePath = $this->applicant->fresh()->passport->file_path;
        $response->assertSee(Storage::url($filePath));
        $response->assertSee('<img', false);
    }

    #[Test]
    public function passport_pdf_file_shows_as_link(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('passport_copy.pdf', 100, 'application/pdf');
        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'passport']), [
                'passport_no' => 'P12345678',
                'file'        => $file,
            ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant));

        $response->assertOk();
        $filePath = $this->applicant->fresh()->passport->file_path;
        $response->assertSee(Storage::url($filePath));
    }
}
