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

class ReportTemplateOrderingTest extends TestCase
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

    // ─── SORT_ORDER COLUMN ───────────────────────────────────────────

    #[Test]
    public function sort_order_column_exists(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('report_templates', 'sort_order')
        );
    }

    #[Test]
    public function sort_order_defaults_to_zero(): void
    {
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $this->assertEquals(0, $template->sort_order);
    }

    #[Test]
    public function templates_are_ordered_by_sort_order_on_index(): void
    {
        ReportTemplate::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Middle', 'sort_order' => 1]);
        ReportTemplate::factory()->create(['agency_id' => $this->agency->id, 'name' => 'First', 'sort_order' => 0]);
        ReportTemplate::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Last', 'sort_order' => 2]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.index'));

        $response->assertOk();
        // Should appear in order: First, Middle, Last
        $content = $response->getContent();
        $firstPos = strpos($content, 'First');
        $middlePos = strpos($content, 'Middle');
        $lastPos = strpos($content, 'Last');

        $this->assertNotFalse($firstPos, 'First template not found');
        $this->assertNotFalse($middlePos, 'Middle template not found');
        $this->assertNotFalse($lastPos, 'Last template not found');
        $this->assertLessThan($middlePos, $firstPos);
        $this->assertLessThan($lastPos, $middlePos);
    }

    #[Test]
    public function edit_form_has_sort_order_field(): void
    {
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('report-templates.edit', $template));

        $response->assertOk();
        $response->assertSee('sort_order');
    }

    #[Test]
    public function can_update_sort_order(): void
    {
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'sort_order' => 0,
        ]);

        $this->actingAs($this->admin)
            ->put(route('report-templates.update', $template), [
                'name' => $template->name,
                'type' => $template->type,
                'template_sort_order' => 5,
            ]);

        $this->assertDatabaseHas('report_templates', [
            'id' => $template->id,
            'sort_order' => 5,
        ]);
    }
}
