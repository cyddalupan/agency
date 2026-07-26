<?php

namespace Tests\Feature\ReportTemplate;

use App\Models\Agency;
use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportTemplateSettingsCrudTest extends TestCase
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
    }

    // ─── INDEX / LIST ─────────────────────────────────────────────────

    #[Test]
    public function admin_can_list_report_templates(): void
    {
        ReportTemplate::factory()->count(2)->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('report-templates.index'));

        $response->assertOk();
        $response->assertViewHas('templates');
    }

    #[Test]
    public function guest_cannot_list_templates(): void
    {
        $response = $this->get(route('report-templates.index'));
        $response->assertRedirect(route('login'));
    }

    // ─── CREATE ───────────────────────────────────────────────────────

    #[Test]
    public function admin_can_see_create_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('report-templates.create'));

        $response->assertOk();
    }

    #[Test]
    public function guest_cannot_see_create_form(): void
    {
        $response = $this->get(route('report-templates.create'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function admin_can_store_new_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('report-templates.store'), [
                'name' => 'Applicant Summary',
                'type' => 'applicant_report',
                'config' => json_encode([
                    'columns' => ['name', 'status', 'country'],
                    'group_by' => 'status',
                    'sort_by' => 'created_at',
                    'sort_order' => 'desc',
                    'date_preset' => 'this_month',
                ]),
                'is_active' => true,
            ]);

        $response->assertRedirect(route('report-templates.index'));
        $this->assertDatabaseHas('report_templates', [
            'agency_id' => $this->agency->id,
            'name' => 'Applicant Summary',
            'type' => 'applicant_report',
        ]);
    }

    #[Test]
    public function template_name_is_required(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('report-templates.store'), [
                'name' => '',
                'type' => 'applicant_report',
            ]);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function template_type_is_required(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('report-templates.store'), [
                'name' => 'Test Template',
                'type' => '',
            ]);

        $response->assertSessionHasErrors('type');
    }

    #[Test]
    public function template_type_must_be_valid(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('report-templates.store'), [
                'name' => 'Test Template',
                'type' => 'invalid_type_xyz',
            ]);

        $response->assertSessionHasErrors('type');
    }

    // ─── EDIT / UPDATE ────────────────────────────────────────────────

    #[Test]
    public function admin_can_see_edit_form(): void
    {
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('report-templates.edit', $template));

        $response->assertOk();
        $response->assertViewHas('template');
    }

    #[Test]
    public function admin_can_update_template(): void
    {
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Old Name',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('report-templates.update', $template), [
                'name' => 'Updated Name',
                'type' => $template->type,
                'config' => json_encode($template->config),
            ]);

        $response->assertRedirect(route('report-templates.index'));
        $this->assertDatabaseHas('report_templates', [
            'id' => $template->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function cannot_edit_template_from_other_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $template = ReportTemplate::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('report-templates.edit', $template));

        $response->assertNotFound();
    }

    // ─── DELETE / DEACTIVATE ──────────────────────────────────────────

    #[Test]
    public function admin_can_delete_template(): void
    {
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->delete(route('report-templates.destroy', $template));

        $response->assertRedirect(route('report-templates.index'));
        $this->assertModelMissing($template);
    }

    #[Test]
    public function cannot_delete_template_from_other_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $template = ReportTemplate::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($this->admin)
            ->delete(route('report-templates.destroy', $template));

        $response->assertNotFound();
        $this->assertModelExists($template);
    }

    // ─── SCOPED TO AGENCY ─────────────────────────────────────────────

    #[Test]
    public function only_sees_own_agency_templates(): void
    {
        $otherAgency = Agency::factory()->create();
        ReportTemplate::factory()->create(['agency_id' => $otherAgency]);
        ReportTemplate::factory()->count(2)->create(['agency_id' => $this->agency]);

        $response = $this->actingAs($this->admin)
            ->get(route('report-templates.index'));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('templates'));
    }

    // ─── SEEDER ───────────────────────────────────────────────────────

    #[Test]
    public function seeder_creates_default_template(): void
    {
        $this->seed(\Database\Seeders\DefaultReportTemplateSeeder::class);

        $this->assertDatabaseHas('report_templates', [
            'name' => 'Default Applicant Report',
        ]);
    }
}
