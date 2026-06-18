<?php

namespace Tests\Feature\AiAssistant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AiAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private string $templatesEndpoint;
    private string $queryEndpoint;
    private string $exportEndpoint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        $this->templatesEndpoint = route('ai.assistant.templates');
        $this->queryEndpoint = route('ai.assistant.query');
        $this->exportEndpoint = route('ai.assistant.export', ['query' => '__placeholder__']);
    }

    // ═══════════════════════════════════════════════════════════════════
    // 1. PRE-BUILT QUERY TEMPLATES
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function unauthenticated_user_cannot_access_templates(): void
    {
        $response = $this->getJson($this->templatesEndpoint);

        $response->assertUnauthorized();
    }

    #[Test]
    public function authenticated_user_can_list_available_templates(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson($this->templatesEndpoint);

        $response->assertOk();
        $response->assertJsonStructure([
            'templates' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'category',
                ],
            ],
        ]);
    }

    #[Test]
    public function templates_include_top_applicants_by_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson($this->templatesEndpoint);

        $response->assertOk();
        $templates = $response->json('templates');
        $templateIds = array_column($templates, 'id');

        $this->assertContains('top_applicants_by_status', $templateIds);
    }

    #[Test]
    public function templates_include_monthly_deployment_stats(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson($this->templatesEndpoint);

        $response->assertOk();
        $templates = $response->json('templates');
        $templateIds = array_column($templates, 'id');

        $this->assertContains('monthly_deployment_stats', $templateIds);
    }

    #[Test]
    public function templates_include_billing_summary(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson($this->templatesEndpoint);

        $response->assertOk();
        $templates = $response->json('templates');
        $templateIds = array_column($templates, 'id');

        $this->assertContains('billing_summary', $templateIds);
    }

    #[Test]
    public function templates_include_employer_rankings(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson($this->templatesEndpoint);

        $response->assertOk();
        $templates = $response->json('templates');
        $templateIds = array_column($templates, 'id');

        $this->assertContains('employer_rankings', $templateIds);
    }

    #[Test]
    public function templates_include_status_pipeline_breakdown(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson($this->templatesEndpoint);

        $response->assertOk();
        $templates = $response->json('templates');
        $templateIds = array_column($templates, 'id');

        $this->assertContains('status_pipeline_breakdown', $templateIds);
    }

    #[Test]
    public function templates_are_categorized_correctly(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson($this->templatesEndpoint);

        $response->assertOk();
        $templates = $response->json('templates');

        $categories = array_unique(array_column($templates, 'category'));
        $expectedCategories = ['applicants', 'deployments', 'billing', 'employers'];

        foreach ($expectedCategories as $category) {
            $this->assertContains($category, $categories);
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // 2. EXECUTE A TEMPLATE QUERY
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function can_execute_a_template_query(): void
    {
        Applicant::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $endpoint = route('ai.assistant.template', ['template' => 'top_applicants_by_status']);

        $response = $this->actingAs($this->admin)
            ->getJson($endpoint);

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'sql',
            'explanation',
            'template_id',
        ]);
        $this->assertEquals('top_applicants_by_status', $response->json('template_id'));
    }

    #[Test]
    public function executing_invalid_template_returns_404(): void
    {
        $endpoint = route('ai.assistant.template', ['template' => 'nonexistent_template']);

        $response = $this->actingAs($this->admin)
            ->getJson($endpoint);

        $response->assertStatus(404);
    }

    #[Test]
    public function template_results_are_scoped_to_current_agency(): void
    {
        $otherAgency = Agency::factory()->create();

        // Create 5 deployed applicants in our agency
        Applicant::factory()->count(5)->create([
            'agency_id' => $this->agency->id,
            'status_code' => 8, // Deployed
        ]);

        // Create 3 deployed applicants in other agency
        Applicant::factory()->count(3)->create([
            'agency_id' => $otherAgency->id,
            'status_code' => 8, // Deployed
        ]);

        $endpoint = route('ai.assistant.template', ['template' => 'top_applicants_by_status']);

        $response = $this->actingAs($this->admin)
            ->getJson($endpoint);

        $response->assertOk();
        $data = $response->json('data');

        // Should only see our agency's 5 deployed applicants, not the other agency's 3
        $deployedStats = collect($data)->firstWhere('status_code', 8);
        $this->assertNotNull($deployedStats);
        $this->assertEquals(5, $deployedStats['total']);
    }

    // ═══════════════════════════════════════════════════════════════════
    // 3. CSV EXPORT OF QUERY RESULTS
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function can_export_query_results_as_csv(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        $exportEndpoint = route('ai.assistant.export', ['query' => 'List all applicants']);

        $response = $this->actingAs($this->admin)
            ->getJson($exportEndpoint);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="ai-query-results.csv"');
    }

    #[Test]
    public function csv_export_contains_column_headers(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('ai.assistant.export', ['query' => 'List all applicants']));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('id', $content);
        $this->assertStringContainsString('first_name', $content);
    }

    #[Test]
    public function csv_export_contains_data_rows(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Maria',
            'last_name' => 'Santos',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('ai.assistant.export', ['query' => 'List all applicants']));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('Maria', $content);
        $this->assertStringContainsString('Santos', $content);
    }

    #[Test]
    public function csv_export_supports_template_queries(): void
    {
        $exportEndpoint = route('ai.assistant.export', ['query' => 'template:top_applicants_by_status']);

        $response = $this->actingAs($this->admin)
            ->getJson($exportEndpoint);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    public function unauthenticated_user_cannot_export_csv(): void
    {
        $response = $this->getJson(route('ai.assistant.export', ['query' => 'List all applicants']));

        $response->assertUnauthorized();
    }

    // ═══════════════════════════════════════════════════════════════════
    // 4. PER-AGENCY DAILY RATE LIMIT
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function agency_has_daily_query_limit(): void
    {
        // Make 101 requests (100/day limit, so 101st should fail)
        for ($i = 0; $i < 101; $i++) {
            $response = $this->actingAs($this->admin)
                ->postJson($this->queryEndpoint, [
                    'query' => "Show me applicant $i",
                ]);
        }

        // The 101st request should be blocked
        $response->assertStatus(429);
        $response->assertJsonStructure(['error', 'retry_after']);
    }

    #[Test]
    public function daily_limit_tracks_per_agency_independently(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAdmin = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);

        // Exhaust daily limit for agency A
        for ($i = 0; $i < 100; $i++) {
            $this->actingAs($this->admin)
                ->postJson($this->queryEndpoint, [
                    'query' => "Query $i",
                ]);
        }

        // Agency A should now be blocked
        $responseA = $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'One more query',
            ]);
        $responseA->assertStatus(429);

        // Agency B should still be allowed
        $responseB = $this->actingAs($otherAdmin)
            ->postJson($this->queryEndpoint, [
                'query' => 'Query from agency B',
            ]);
        $responseB->assertOk();
    }

    #[Test]
    public function daily_limit_resets_after_settings_update(): void
    {
        // First query works
        $response = $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'Show me applicants',
            ]);
        $response->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'agency_id' => $this->agency->id,
            'action' => 'ai_query',
        ]);
    }

    #[Test]
    public function daily_query_count_is_tracked_in_activity_logs(): void
    {
        $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'Query 1',
            ]);
        $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'Query 2',
            ]);

        $count = \App\Models\ActivityLog::where('agency_id', $this->agency->id)
            ->where('action', 'ai_query')
            ->whereDate('created_at', today())
            ->count();

        $this->assertEquals(2, $count);
    }
}
