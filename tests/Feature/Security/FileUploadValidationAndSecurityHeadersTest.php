<?php

namespace Tests\Feature\Security;

use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FileUploadValidationAndSecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    // ═══════════════════════════════════════════════════════════════════
    // FILE UPLOAD VALIDATION
    // ═══════════════════════════════════════════════════════════════════

    // ─── ALLOWED FILE TYPES ──────────────────────────────────────────

    #[Test]
    public function document_upload_accepts_pdf_files(): void
    {
        Storage::fake('public');
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'passport',
                'document'      => $file,
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('applicant_documents', [
            'applicant_id' => $applicant->id,
            'mime_type'    => 'application/pdf',
        ]);
    }

    #[Test]
    public function document_upload_accepts_jpg_files(): void
    {
        Storage::fake('public');
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'photo',
                'document'      => $file,
            ]);

        $response->assertSessionHas('success');
    }

    #[Test]
    public function document_upload_accepts_png_files(): void
    {
        Storage::fake('public');
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('document.png', 100, 'image/png');

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'photo',
                'document'      => $file,
            ]);

        $response->assertSessionHas('success');
    }

    // ─── REJECTED FILE TYPES ─────────────────────────────────────────

    #[Test]
    public function document_upload_rejects_executable_files(): void
    {
        Storage::fake('public');
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'passport',
                'document'      => $file,
            ]);

        $response->assertSessionHasErrors('document');
    }

    #[Test]
    public function document_upload_rejects_php_files(): void
    {
        Storage::fake('public');
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('shell.php', 100, 'text/plain');

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'passport',
                'document'      => $file,
            ]);

        $response->assertSessionHasErrors('document');
    }

    #[Test]
    public function document_upload_rejects_html_files(): void
    {
        Storage::fake('public');
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('page.html', 100, 'text/html');

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'passport',
                'document'      => $file,
            ]);

        $response->assertSessionHasErrors('document');
    }

    #[Test]
    public function document_upload_rejects_svg_files(): void
    {
        Storage::fake('public');
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('image.svg', 100, 'image/svg+xml');

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'passport',
                'document'      => $file,
            ]);

        $response->assertSessionHasErrors('document');
    }

    #[Test]
    public function document_upload_rejects_dangerous_mime_types(): void
    {
        Storage::fake('public');
        $applicant = Applicant::factory()->create();

        $dangerousMimes = [
            ['name' => 'script.sh', 'mime' => 'application/x-sh'],
            ['name' => 'batch.bat', 'mime' => 'application/x-bat'],
            ['name' => 'cmd.msi', 'mime' => 'application/x-msi'],
            ['name' => 'archive.jar', 'mime' => 'application/java-archive'],
        ];

        foreach ($dangerousMimes as $dangerous) {
            $file = UploadedFile::fake()->create($dangerous['name'], 100, $dangerous['mime']);

            $response = $this->actingAs($applicant, 'applicant')
                ->post(route('portal.documents.upload'), [
                    'document_type' => 'passport',
                    'document'      => $file,
                ]);

            $response->assertSessionHasErrors('document');
        }
    }

    // ─── FILE SIZE VALIDATION ────────────────────────────────────────

    #[Test]
    public function document_upload_rejects_oversized_files(): void
    {
        Storage::fake('public');
        $applicant = Applicant::factory()->create();

        // 6MB file > 5MB limit
        $file = UploadedFile::fake()->create('large.pdf', 6000, 'application/pdf');

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'passport',
                'document'      => $file,
            ]);

        $response->assertSessionHasErrors('document');
    }

    #[Test]
    public function document_upload_allows_within_size_limit(): void
    {
        Storage::fake('public');
        $applicant = Applicant::factory()->create();

        // 4MB file within 5MB limit
        $file = UploadedFile::fake()->create('reasonable.pdf', 4000, 'application/pdf');

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'passport',
                'document'      => $file,
            ]);

        $response->assertSessionHas('success');
    }

    // ─── VALIDATION RULES ────────────────────────────────────────────

    #[Test]
    public function document_type_is_required(): void
    {
        Storage::fake('public');
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document' => $file,
            ]);

        $response->assertSessionHasErrors('document_type');
    }

    #[Test]
    public function document_file_is_required(): void
    {
        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'passport',
            ]);

        $response->assertSessionHasErrors('document');
    }

    #[Test]
    public function document_type_must_not_exceed_max_length(): void
    {
        Storage::fake('public');
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => str_repeat('x', 51),
                'document'      => $file,
            ]);

        $response->assertSessionHasErrors('document_type');
    }

    #[Test]
    public function notes_field_must_not_exceed_max_length(): void
    {
        Storage::fake('public');
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.documents.upload'), [
                'document_type' => 'passport',
                'document'      => $file,
                'notes'         => str_repeat('x', 501),
            ]);

        $response->assertSessionHasErrors('notes');
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECURITY HEADERS
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function response_has_x_content_type_options_header(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    #[Test]
    public function response_has_x_frame_options_header(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    #[Test]
    public function response_has_x_xss_protection_header(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    #[Test]
    public function response_has_referrer_policy_header(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    #[Test]
    public function response_has_permissions_policy_header(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('Permissions-Policy');
        $header = $response->headers->get('Permissions-Policy');
        $this->assertNotEmpty($header);
    }

    #[Test]
    public function response_has_strict_transport_security_header(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('Strict-Transport-Security');
        $header = $response->headers->get('Strict-Transport-Security');
        $this->assertStringContainsString('max-age=', $header);
    }

    #[Test]
    public function response_has_content_security_policy_header(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('Content-Security-Policy');
        $header = $response->headers->get('Content-Security-Policy');
        $this->assertNotEmpty($header);
    }

    #[Test]
    public function response_does_not_leak_server_information(): void
    {
        $response = $this->get('/login');

        $this->assertNull(
            $response->headers->get('X-Powered-By'),
            'Response should not expose X-Powered-By header'
        );
    }

    #[Test]
    public function response_does_not_leak_server_header(): void
    {
        $response = $this->get('/login');

        $this->assertNull(
            $response->headers->get('Server'),
            'Response should not expose Server header'
        );
    }

    #[Test]
    public function security_headers_present_on_all_routes(): void
    {
        $routes = ['/login', '/dashboard', '/agency/dashboard'];

        // Authenticated routes
        $admin = User::factory()->create(['user_type' => 'admin']);
        $adminRoutes = ['/dashboard', '/agency/dashboard'];

        foreach ($adminRoutes as $route) {
            $response = $this->actingAs($admin)->get($route);
            $response->assertHeader('X-Content-Type-Options', 'nosniff');
            $response->assertHeader('X-Frame-Options', 'DENY');
            $response->assertHeader('X-XSS-Protection', '1; mode=block');
            $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertHeader('X-Content-Type-Options', 'nosniff');
            $response->assertHeader('X-Frame-Options', 'DENY');
            $response->assertHeader('X-XSS-Protection', '1; mode=block');
        }
    }

    #[Test]
    public function cached_pages_also_have_security_headers(): void
    {
        // Make two requests to ensure cache doesn't strip headers
        $this->get('/login');
        $response = $this->get('/login');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    #[Test]
    public function applicant_portal_pages_have_security_headers(): void
    {
        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.dashboard'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    #[Test]
    public function error_pages_do_not_expose_sensitive_info(): void
    {
        $response = $this->get('/nonexistent-page-that-will-404');

        // Should get 404, not debug error page with stack trace
        $response->assertStatus(404);

        $content = $response->getContent();
        $this->assertStringNotContainsString('Whoops', $content);
        $this->assertStringNotContainsString('Stack trace', $content);
        $this->assertStringNotContainsString('app_path', $content);
    }
}
