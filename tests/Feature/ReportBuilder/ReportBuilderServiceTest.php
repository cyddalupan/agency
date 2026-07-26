<?php

namespace Tests\Feature\ReportBuilder;

use App\Models\Applicant;
use App\Models\Agency;
use App\Models\Country;
use App\Models\Employer;
use App\Models\ReportTemplate;
use App\Models\StatusCode;
use App\Models\User;
use App\Services\ReportBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportBuilderServiceTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private StatusCode $statusCode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->actingAs($this->user);

        $this->statusCode = StatusCode::factory()->create(['code' => 1, 'label' => 'Pending', 'color' => '#f59e0b']);
    }

    // ─── SERVICE EXISTS ──────────────────────────────────────────────

    #[Test]
    public function report_builder_service_exists(): void
    {
        $builder = app(ReportBuilder::class);
        $this->assertNotNull($builder);
    }

    // ─── BUILD FROM TEMPLATE ────────────────────────────────────────

    #[Test]
    public function can_build_query_from_template(): void
    {
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['name', 'email', 'status'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'date_preset' => null,
            ],
        ]);

        $builder = app(ReportBuilder::class);
        $result = $builder->fromTemplate($template)->get();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
    }

    // ─── COLUMN MAPPING ──────────────────────────────────────────────

    #[Test]
    public function returns_selected_columns_from_template(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id, 'first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'email' => 'juan@example.com']);

        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['name', 'email', 'status'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'date_preset' => null,
            ],
        ]);

        $result = app(ReportBuilder::class)->fromTemplate($template)->get();

        $this->assertCount(1, $result);
        $row = $result->first();
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('email', $row);
        $this->assertArrayHasKey('status', $row);
    }

    #[Test]
    public function resolves_name_column_from_first_and_last(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Maria',
            'last_name' => 'Santos',
        ]);

        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['name', 'created_at'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'asc',
                'date_preset' => null,
            ],
        ]);

        $result = app(ReportBuilder::class)->fromTemplate($template)->get();
        $row = $result->first();

        $this->assertEquals('Maria Santos', $row['name']);
    }

    #[Test]
    public function resolves_status_from_status_code_label(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'status_code' => $this->statusCode->code,
        ]);

        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['status', 'created_at'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'asc',
                'date_preset' => null,
            ],
        ]);

        $result = app(ReportBuilder::class)->fromTemplate($template)->get();
        $row = $result->first();

        $this->assertEquals('Pending', $row['status']);
    }

    #[Test]
    public function resolves_country_from_country_name(): void
    {
        $country = Country::factory()->create(['name' => 'Canada']);
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'country_id' => $country->id,
        ]);

        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['country', 'created_at'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'asc',
                'date_preset' => null,
            ],
        ]);

        $result = app(ReportBuilder::class)->fromTemplate($template)->get();
        $row = $result->first();

        $this->assertEquals('Canada', $row['country']);
    }

    #[Test]
    public function resolves_employer_from_employer_name(): void
    {
        $employer = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Tech Corp']);
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $employer->id,
        ]);

        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['employer', 'name', 'email'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'asc',
                'date_preset' => null,
            ],
        ]);

        $result = app(ReportBuilder::class)->fromTemplate($template)->get();
        $row = $result->first();

        $this->assertEquals('Tech Corp', $row['employer']);
    }

    // ─── SORTING ─────────────────────────────────────────────────────

    #[Test]
    public function sorts_by_date_descending(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id, 'created_at' => now()->subDay()]);
        Applicant::factory()->create(['agency_id' => $this->agency->id, 'created_at' => now()]);

        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['name', 'created_at'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'date_preset' => null,
            ],
        ]);

        $result = app(ReportBuilder::class)->fromTemplate($template)->get();
        $dates = $result->pluck('created_at')->values();

        $this->assertTrue($dates[0] >= $dates[1]);
    }

    #[Test]
    public function sorts_by_date_ascending(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id, 'created_at' => now()->subDay()]);
        Applicant::factory()->create(['agency_id' => $this->agency->id, 'created_at' => now()]);

        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['name', 'created_at'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'asc',
                'date_preset' => null,
            ],
        ]);

        $result = app(ReportBuilder::class)->fromTemplate($template)->get();
        $dates = $result->pluck('created_at')->values();

        $this->assertTrue($dates[0] <= $dates[1]);
    }

    // ─── DATE PRESETS ────────────────────────────────────────────────

    #[Test]
    public function date_preset_this_month_filters_correctly(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id, 'created_at' => now()->startOfMonth()->addDay()]);
        Applicant::factory()->create(['agency_id' => $this->agency->id, 'created_at' => now()->subMonth()]);

        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['name', 'created_at'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'asc',
                'date_preset' => 'this_month',
            ],
        ]);

        $result = app(ReportBuilder::class)->fromTemplate($template)->get();

        $this->assertCount(1, $result);
    }

    // ─── SCOPED TO AGENCY ────────────────────────────────────────────

    #[Test]
    public function only_returns_own_agency_data(): void
    {
        $otherAgency = Agency::factory()->create();
        Applicant::factory()->create(['agency_id' => $this->agency->id]);
        Applicant::factory()->create(['agency_id' => $otherAgency->id]);

        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['name'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'date_preset' => null,
            ],
        ]);

        $result = app(ReportBuilder::class)->fromTemplate($template)->get();

        $this->assertCount(1, $result);
    }

    // ─── EMPTY RESULT ────────────────────────────────────────────────

    #[Test]
    public function returns_empty_collection_when_no_data(): void
    {
        $template = ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['name'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'date_preset' => null,
            ],
        ]);

        $result = app(ReportBuilder::class)->fromTemplate($template)->get();

        $this->assertTrue($result->isEmpty());
    }

    // ─── THROWS FOR UNKNOWN TYPE ─────────────────────────────────────

    #[Test]
    public function throws_exception_for_unsupported_report_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ReportBuilder::forType('unknown_type');
    }
}
