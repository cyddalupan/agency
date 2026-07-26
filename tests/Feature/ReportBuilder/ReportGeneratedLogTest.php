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

class ReportGeneratedLogTest extends TestCase
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

        StatusCode::factory()->create(['code' => 1, 'label' => 'Pending']);
    }

    // ─── AUDIT LOG TABLE ─────────────────────────────────────────────

    #[Test]
    public function report_generated_log_table_exists(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasTable('report_generated_logs')
        );
    }

    #[Test]
    public function log_has_expected_columns(): void
    {
        $columns = ['id', 'agency_id', 'user_id', 'report_template_id', 'format', 'created_at'];
        foreach ($columns as $col) {
            $this->assertTrue(
                \Illuminate\Support\Facades\Schema::hasColumn('report_generated_logs', $col),
                "Missing column: {$col}"
            );
        }
    }

    // ─── LOGGING ON GENERATION ───────────────────────────────────────

    #[Test]
    public function pdf_generation_creates_log_entry(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin)
            ->get(route('reports.pdf', $template));

        $this->assertDatabaseHas('report_generated_logs', [
            'agency_id' => $this->agency->id,
            'user_id' => $this->admin->id,
            'report_template_id' => $template->id,
            'format' => 'pdf',
        ]);
    }

    #[Test]
    public function csv_export_creates_log_entry(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin)
            ->get(route('reports.csv', $template));

        $this->assertDatabaseHas('report_generated_logs', [
            'agency_id' => $this->agency->id,
            'user_id' => $this->admin->id,
            'report_template_id' => $template->id,
            'format' => 'csv',
        ]);
    }

    #[Test]
    public function preview_does_not_create_log_entry(): void
    {
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin)
            ->get(route('reports.preview', $template));

        $this->assertDatabaseMissing('report_generated_logs', [
            'report_template_id' => $template->id,
        ]);
    }

    #[Test]
    public function generated_logs_are_scoped_to_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAdmin = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($otherAdmin)
            ->get(route('reports.pdf', $template));

        // Other agency shouldn't be able to download, so no log for this agency
        $this->assertDatabaseMissing('report_generated_logs', [
            'agency_id' => $this->agency->id,
            'format' => 'pdf',
        ]);
    }
}
