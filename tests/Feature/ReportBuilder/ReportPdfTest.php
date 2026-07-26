<?php

namespace Tests\Feature\ReportBuilder;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ReportTemplate;
use App\Models\StatusCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportPdfTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create([
            'name' => 'Test Agency PH',
            'logo' => null,
            'address' => '123 Main St, Manila',
        ]);
        app()->instance('tenant_agency', $this->agency);

        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        StatusCode::factory()->create(['code' => 1, 'label' => 'Pending', 'color' => '#f59e0b']);
    }

    // ─── PDF DOWNLOAD ────────────────────────────────────────────────

    #[Test]
    public function pdf_download_returns_pdf_response(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.pdf', $template));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    #[Test]
    public function pdf_has_template_name_in_filename(): void
    {
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Custom Filename Test',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.pdf', $template));

        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('custom-filename-test', $response->headers->get('Content-Disposition'));
    }

    private function pdfText(\Illuminate\Testing\TestResponse $response): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'pdf-test-') . '.pdf';
        file_put_contents($tmp, $response->getContent());
        $text = shell_exec("pdftotext '$tmp' - 2>/dev/null") ?? '';
        @unlink($tmp);
        return $text;
    }

    #[Test]
    public function pdf_has_agency_branding(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.pdf', $template));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('Test Agency PH', $this->pdfText($response));
    }

    #[Test]
    public function pdf_shows_report_data(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.pdf', $template));

        $this->assertStringContainsString('Juan Dela Cruz', $this->pdfText($response));
    }

    #[Test]
    public function guest_cannot_download_pdf(): void
    {
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->get(route('reports.pdf', $template));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function cannot_download_pdf_from_other_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $template = ReportTemplate::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.pdf', $template));

        $response->assertNotFound();
    }

    #[Test]
    public function pdf_has_report_title(): void
    {
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Monthly Report Q3',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.pdf', $template));

        $this->assertStringContainsString('Monthly Report Q3', $this->pdfText($response));
    }

    // ─── CSV EXPORT ──────────────────────────────────────────────────

    #[Test]
    public function csv_export_returns_csv_response(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.csv', $template));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    #[Test]
    public function csv_has_header_row(): void
    {
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['name', 'email', 'gender'],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.csv', $template));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Name', $content);
        $this->assertStringContainsString('Email', $content);
        $this->assertStringContainsString('Gender', $content);
    }

    #[Test]
    public function csv_has_data_rows(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Pedro',
            'last_name' => 'Garcia',
            'email' => 'pedro@example.com',
        ]);
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['name', 'email', 'gender'],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.csv', $template));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Pedro Garcia', $content);
        $this->assertStringContainsString('pedro@example.com', $content);
    }

    #[Test]
    public function csv_headers_include_download_disposition(): void
    {
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Applicant Export',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.csv', $template));

        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('applicant-export', $response->headers->get('Content-Disposition'));
    }

    #[Test]
    public function guest_cannot_export_csv(): void
    {
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->get(route('reports.csv', $template));
        $response->assertRedirect(route('login'));
    }
}
