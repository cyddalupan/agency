<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS "Personal Information" — PI: 5. Upload Files tab (TDD).
 *
 * The Upload Files tab must offer a file input and list uploaded documents
 * showing the Encoder (admin who uploaded) and the Date Uploaded.
 */
class PersonalInformationUploadFilesTabTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'name'      => 'Encoder One',
        ]);
        $this->applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => 'with',
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    private function getShowHtml(): string
    {
        return $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();
    }

    #[Test]
    public function upload_files_tab_renders_file_input(): void
    {
        $html = $this->getShowHtml();

        $this->assertStringContainsString('upload', $html, 'tab button present');
        $this->assertStringContainsString('Upload Files', $html);
        $this->assertStringContainsString('Document Type', $html);
        $this->assertStringContainsString('type="file"', $html, 'file input present');
        $this->assertStringContainsString('/documents', $html, 'upload form action present');
        $this->assertStringContainsString('enctype="multipart/form-data"', $html, 'multipart form');
    }

    #[Test]
    public function admin_can_upload_a_document_which_persists(): void
    {
        $file = UploadedFile::fake()->image('passport_copy.jpg', 400, 300);

        $response = $this->actingAs($this->user)->post(
            route('applicants.documents.store', $this->applicant),
            [
                'document_type' => 'passport_copy',
                'file'          => $file,
                'notes'         => 'clear scan',
            ]
        );

        $response->assertRedirect(route('applicants.show', $this->applicant));
        $response->assertSessionHas('success');

        $doc = $this->applicant->documents()->first();
        $this->assertNotNull($doc, 'document persisted');
        $this->assertSame('passport_copy', $doc->document_type);
        $this->assertSame('clear scan', $doc->notes);
        Storage::disk('public')->assertExists($doc->file_path);
    }

    #[Test]
    public function upload_records_the_encoder_admin_user(): void
    {
        $file = UploadedFile::fake()->image('contract.jpg', 400, 300);

        $this->actingAs($this->user)->post(
            route('applicants.documents.store', $this->applicant),
            ['document_type' => 'contract', 'file' => $file]
        );

        $this->assertDatabaseHas('applicant_documents', [
            'applicant_id' => $this->applicant->id,
            'document_type' => 'contract',
            'user_id'      => $this->user->id,
        ]);
    }

    #[Test]
    public function uploaded_document_list_shows_encoder_name_and_date_uploaded(): void
    {
        $doc = ApplicantDocument::create([
            'agency_id'     => $this->agency->id,
            'applicant_id'  => $this->applicant->id,
            'user_id'       => $this->user->id,
            'document_type' => 'nbi_clearance',
            'file_name'     => 'nbi.pdf',
            'file_path'     => 'applicant-sub-files/nbi.pdf',
            'mime_type'     => 'application/pdf',
            'file_size'     => 100,
            'created_at'    => now()->subDays(3),
            'updated_at'    => now()->subDays(3),
        ]);

        $html = $this->getShowHtml();

        // Encoder name
        $this->assertStringContainsString('Encoder One', $html);
        // Date Uploaded (formatted created_at, e.g. Aug 03, 2026)
        $this->assertStringContainsString($doc->created_at->format('M d, Y'), $html);
        // File name still shown
        $this->assertStringContainsString('nbi.pdf', $html);
        $this->assertStringContainsString('nbi clearance', $html);
    }

    #[Test]
    public function document_delete_removes_record_and_file(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 200, 200);
        $this->actingAs($this->user)->post(
            route('applicants.documents.store', $this->applicant),
            ['document_type' => 'photo', 'file' => $file]
        );
        $doc = $this->applicant->documents()->first();

        $this->actingAs($this->user)->delete(
            route('applicants.documents.destroy', [$this->applicant, $doc])
        )->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseMissing('applicant_documents', ['id' => $doc->id]);
        Storage::disk('public')->assertMissing($doc->file_path);
    }
}
