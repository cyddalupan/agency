<?php

namespace Tests\Feature\ReportTemplate;

use App\Models\Agency;
use App\Models\ReportTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportTemplateModelTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agency = Agency::factory()->create();
    }

    #[Test]
    public function can_create_a_report_template(): void
    {
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Applicant Summary',
            'type' => 'applicant_report',
            'config' => [
                'columns' => ['name', 'status', 'country', 'created_at'],
                'group_by' => 'status',
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'date_preset' => 'this_month',
            ],
        ]);

        $this->assertInstanceOf(ReportTemplate::class, $template);
        $this->assertEquals('Applicant Summary', $template->name);
        $this->assertEquals('applicant_report', $template->type);
        $this->assertTrue($template->is_active);
        $this->assertCount(4, $template->config['columns']);
    }

    #[Test]
    public function belongs_to_agency(): void
    {
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $this->assertTrue($template->agency->is($this->agency));
    }

    #[Test]
    public function config_is_cast_to_array(): void
    {
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => ['columns' => ['name'], 'group_by' => null],
        ]);

        $this->assertIsArray($template->config);
        $this->assertEquals(['name'], $template->config['columns']);
    }

    #[Test]
    public function is_active_defaults_to_true(): void
    {
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $this->assertTrue($template->is_active);
    }

    #[Test]
    public function can_be_deactivated(): void
    {
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'is_active' => false,
        ]);

        $this->assertFalse($template->is_active);
    }

    #[Test]
    public function scoped_to_active_templates(): void
    {
        ReportTemplate::factory()->count(2)->create([
            'agency_id' => $this->agency->id,
            'is_active' => true,
        ]);
        ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'is_active' => false,
        ]);

        $active = ReportTemplate::active()->get();

        $this->assertCount(2, $active);
    }

    #[Test]
    public function scoped_by_type(): void
    {
        ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'type' => 'applicant_report',
        ]);
        ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'type' => 'statistics',
        ]);

        $applicantReports = ReportTemplate::byType('applicant_report')->get();

        $this->assertCount(1, $applicantReports);
        $this->assertEquals('applicant_report', $applicantReports->first()->type);
    }

    #[Test]
    public function has_required_columns(): void
    {
        $this->assertTrue(
            \Schema::hasColumns('report_templates', [
                'id', 'agency_id', 'name', 'type', 'config', 'is_active', 'created_at', 'updated_at',
            ])
        );
    }
}
