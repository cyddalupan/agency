<?php

namespace Tests\Feature\Portal;

use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantDocumentUploadTest extends TestCase
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

    #[Test]
    public function authenticated_applicant_can_upload_document(): void
    {
        $file = UploadedFile::fake()->create('resume.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'resume',
                'document'      => $file,
                'notes'         => 'My updated resume',
            ]);

        $response->assertRedirect(route('portal.profile'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('applicant_documents', [
            'applicant_id'  => $this->applicant->id,
            'document_type' => 'resume',
            'file_name'     => 'resume.pdf',
            'notes'         => 'My updated resume',
        ]);
    }

    #[Test]
    public function unauthenticated_applicant_cannot_upload(): void
    {
        $file = UploadedFile::fake()->create('resume.pdf', 200, 'application/pdf');

        $response = $this->post(route('portal.documents.upload'), [
            'document_type' => 'resume',
            'document'      => $file,
        ]);

        $response->assertRedirect(route('portal.login'));
    }

    #[Test]
    public function document_requires_valid_type(): void
    {
        $file = UploadedFile::fake()->create('test.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => '',
                'document'      => $file,
            ]);

        $response->assertSessionHasErrors('document_type');
    }

    #[Test]
    public function document_requires_valid_file(): void
    {
        $response = $this->actingAs($this->applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'resume',
                'document'      => null,
            ]);

        $response->assertSessionHasErrors('document');
    }

    #[Test]
    public function document_must_be_pdf_jpg_or_png(): void
    {
        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $response = $this->actingAs($this->applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'resume',
                'document'      => $file,
            ]);

        $response->assertSessionHasErrors('document');
    }

    #[Test]
    public function document_must_not_exceed_max_size(): void
    {
        $file = UploadedFile::fake()->create('large.pdf', 6000, 'application/pdf');

        $response = $this->actingAs($this->applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'resume',
                'document'      => $file,
            ]);

        $response->assertSessionHasErrors('document');
    }

    #[Test]
    public function profile_page_shows_uploaded_documents(): void
    {
        ApplicantDocument::create([
            'agency_id'     => $this->agency->id,
            'applicant_id'  => $this->applicant->id,
            'document_type' => 'resume',
            'file_name'     => 'resume.pdf',
            'file_path'     => 'applicant-documents/resume.pdf',
            'mime_type'     => 'application/pdf',
            'file_size'     => 2048,
        ]);

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Documents');
        $response->assertSee('resume.pdf');
    }
}
