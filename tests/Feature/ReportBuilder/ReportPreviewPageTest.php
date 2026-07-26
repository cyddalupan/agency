<?php

namespace Tests\Feature\ReportBuilder;

use App\Models\Applicant;
use App\Models\Agency;
use App\Models\ReportTemplate;
use App\Models\StatusCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportPreviewPageTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        StatusCode::factory()->create(['code' => 1, 'label' => 'Pending', 'color' => '#f59e0b']);
    }

    // ─── REPORTS INDEX ───────────────────────────────────────────────

    #[Test]
    public function reports_index_shows_templates_as_clickable_cards(): void
    {
        $templates = ReportTemplate::factory()->count(2)->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.index'));

        $response->assertOk();
        foreach ($templates as $t) {
            $response->assertSee($t->name);
        }
    }

    #[Test]
    public function guest_cannot_access_reports_index(): void
    {
        $response = $this->get(route('reports.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function reports_index_shows_generate_button_on_each_template(): void
    {
        ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.index'));

        $response->assertSee('Generate');
    }

    // ─── REPORT PREVIEW ──────────────────────────────────────────────

    #[Test]
    public function can_preview_report_from_template(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.preview', $template));

        $response->assertOk();
    }

    #[Test]
    public function preview_shows_data_table(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.preview', $template));

        $response->assertOk();
        $response->assertSee('Juan Dela Cruz');
    }

    #[Test]
    public function preview_shows_template_name(): void
    {
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Preview Test Report',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.preview', $template));

        $response->assertSee('Preview Test Report');
    }

    #[Test]
    public function preview_shows_generate_pdf_button(): void
    {
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.preview', $template));

        $response->assertSee('PDF');
    }

    #[Test]
    public function guest_cannot_access_preview(): void
    {
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->get(route('reports.preview', $template));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function cannot_preview_template_from_other_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $template = ReportTemplate::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.preview', $template));

        $response->assertNotFound();
    }

    #[Test]
    public function preview_shows_column_headers_from_template_config(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['name', 'email', 'gender'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'date_preset' => null,
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.preview', $template));

        // Column headers visible in the table
        $response->assertSee('Name');
        $response->assertSee('Email');
        $response->assertSee('Gender');
    }

    #[Test]
    public function preview_shows_empty_state_when_no_results(): void
    {
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.preview', $template));

        $response->assertOk();
        $response->assertSee('No data');
    }

    #[Test]
    public function preview_shows_coming_soon_for_unsupported_type(): void
    {
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'type' => 'statistics',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.preview', $template));

        $response->assertOk();
        $response->assertSee('coming soon');
    }
}
