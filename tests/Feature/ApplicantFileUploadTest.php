<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;

class ApplicantFileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $agency;
    protected $applicant;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create(['agency_id' => $this->agency->id]);
        $this->applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->user);
    }

    #[Test]
    public function it_can_upload_a_file_to_certificates_sub_table(): void
    {
        $file = UploadedFile::fake()->image('tesda_cert.jpg', 400, 300);

        $response = $this->post(
            route('applicants.sub.store', [$this->applicant, 'certificates']),
            [
                'type' => 'tesda',
                'certificate_no' => 'TESDA-001',
                'file' => $file,
            ]
        );

        $response->assertRedirect(route('applicants.show', $this->applicant));
        $response->assertSessionHas('success');

        $cert = $this->applicant->certificates()->first();
        $this->assertNotNull($cert->file_path);
        Storage::disk('public')->assertExists($cert->file_path);
    }

    #[Test]
    public function it_rejects_oversized_upload(): void
    {
        $file = UploadedFile::fake()->image('large.jpg')->size(3000); // 3MB > 2MB limit

        $response = $this->post(
            route('applicants.sub.store', [$this->applicant, 'certificates']),
            [
                'type' => 'tesda',
                'file' => $file,
            ]
        );

        $response->assertSessionHasErrors('file', null, 'certificates');
    }

    #[Test]
    public function it_rejects_non_image_uploads(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post(
            route('applicants.sub.store', [$this->applicant, 'requirements']),
            [
                'type' => 'nbi',
                'file' => $file,
            ]
        );

        $response->assertSessionHasErrors('file', null, 'requirements');
    }

    #[Test]
    public function it_uploads_to_applicant_documents_table(): void
    {
        $file = UploadedFile::fake()->image('passport_scan.jpg', 800, 600);

        $response = $this->post(
            route('applicants.documents.store', $this->applicant),
            [
                'document_type' => 'passport_copy',
                'file' => $file,
                'notes' => 'Main passport copy',
            ]
        );

        $response->assertRedirect(route('applicants.show', $this->applicant));

        $doc = $this->applicant->documents()->first();
        $this->assertNotNull($doc);
        $this->assertEquals('passport_copy', $doc->document_type);
        $this->assertEquals('passport_scan.jpg', $doc->file_name);
        $this->assertNotNull($doc->file_path);
        Storage::disk('public')->assertExists($doc->file_path);
    }

    #[Test]
    public function it_can_delete_an_applicant_document(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 400, 300);
        $path = $file->store('applicant-documents', 'public');

        $doc = $this->applicant->documents()->create([
            'agency_id' => $this->agency->id,
            'document_type' => 'photo',
            'file_name' => 'test.jpg',
            'file_path' => $path,
            'mime_type' => 'image/jpeg',
            'file_size' => 1000,
        ]);

        $response = $this->delete(
            route('applicants.documents.destroy', [$this->applicant, $doc])
        );

        $response->assertRedirect(route('applicants.show', $this->applicant));
        $this->assertDatabaseMissing('applicant_documents', ['id' => $doc->id]);
        Storage::disk('public')->assertMissing($doc->file_path);
    }

    #[Test]
    public function it_links_an_applicant_to_an_employer(): void
    {
        $employer = Employer::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->patch(
            route('applicants.update', $this->applicant),
            [
                'first_name'  => $this->applicant->first_name,
                'last_name'   => $this->applicant->last_name,
                'employer_id' => $employer->id,
            ]
        );

        $response->assertRedirect(route('applicants.index'));
        $this->assertDatabaseHas('applicants', [
            'id' => $this->applicant->id,
            'employer_id' => $employer->id,
        ]);
    }

    #[Test]
    public function applicant_show_page_shows_employer_name_when_linked(): void
    {
        $employer = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'ABC Corp']);
        $this->applicant->update(['employer_id' => $employer->id]);

        $response = $this->get(route('applicants.show', $this->applicant));
        $response->assertOk();
        $response->assertSee('ABC Corp');
    }

    #[Test]
    public function applicant_show_page_shows_file_thumbnails(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 200, 200);
        $path = $file->store('applicant-documents', 'public');

        $this->applicant->documents()->create([
            'agency_id' => $this->agency->id,
            'document_type' => 'passport_copy',
            'file_name' => 'photo.jpg',
            'file_path' => $path,
            'mime_type' => 'image/jpeg',
            'file_size' => 1000,
        ]);

        $response = $this->get(route('applicants.show', $this->applicant));
        $response->assertOk();
        $response->assertSee('photo.jpg');
        $response->assertSee('storage/');
    }
}
