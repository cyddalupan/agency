<?php

namespace Tests\Feature\AiAssistant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private string $queryEndpoint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        $this->queryEndpoint = route('ai.assistant.query');
    }

    // ═══════════════════════════════════════════════════════════════════
    // 1. AUTHENTICATION & AUTHORIZATION
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function unauthenticated_user_cannot_query_ai_assistant(): void
    {
        $response = $this->postJson($this->queryEndpoint, [
            'query' => 'Show me all applicants',
        ]);

        $response->assertUnauthorized();
    }

    #[Test]
    public function authenticated_user_can_query_ai_assistant(): void
    {
        Applicant::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'Show me all applicants',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'sql',
            'explanation',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════
    // 2. INPUT VALIDATION
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function query_is_required(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['query']);
    }

    #[Test]
    public function query_must_not_be_empty_string(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['query']);
    }

    // ═══════════════════════════════════════════════════════════════════
    // 3. SELECT-ONLY ENFORCEMENT (SECURITY CRITICAL)
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function rejects_insert_queries(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'Insert a new applicant named John',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'Only SELECT queries are allowed.',
        ]);
    }

    #[Test]
    public function rejects_update_queries(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'Update all applicants status to deployed',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'Only SELECT queries are allowed.',
        ]);
    }

    #[Test]
    public function rejects_delete_queries(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'Delete all applicants with cancelled status',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'Only SELECT queries are allowed.',
        ]);
    }

    #[Test]
    public function rejects_drop_queries(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'Drop the applicants table',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'Only SELECT queries are allowed.',
        ]);
    }

    #[Test]
    public function rejects_alter_queries(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'Alter the applicants table to add a column',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'Only SELECT queries are allowed.',
        ]);
    }

    #[Test]
    public function rejects_truncate_queries(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'Truncate the employers table',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'Only SELECT queries are allowed.',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════
    // 4. AGENCY SCOPING
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function results_are_scoped_to_current_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAdmin = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);

        // Create applicants in both agencies
        $myApplicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Alice',
        ]);
        Applicant::factory()->create([
            'agency_id' => $otherAgency->id,
            'first_name' => 'Bob',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'List all applicants named Alice',
            ]);

        $response->assertOk();
        $responseData = $response->json('data');

        // Should only see Alice (own agency), not Bob (other agency)
        $this->assertCount(1, $responseData);
        $this->assertEquals($myApplicant->id, $responseData[0]['id']);
        $this->assertEquals('Alice', $responseData[0]['first_name']);
    }

    #[Test]
    public function user_from_another_agency_cannot_see_our_data(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAdmin = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);

        Applicant::factory()->count(5)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($otherAdmin)
            ->postJson($this->queryEndpoint, [
                'query' => 'List all applicants',
            ]);

        $response->assertOk();
        $responseData = $response->json('data');

        // Other agency user should see zero applicants from our agency
        $this->assertCount(0, $responseData);
    }

    // ═══════════════════════════════════════════════════════════════════
    // 5. RATE LIMITING
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function ai_queries_are_rate_limited(): void
    {
        // Make 31 requests in quick succession (over the 30/min limit)
        for ($i = 0; $i < 31; $i++) {
            $response = $this->actingAs($this->admin)
                ->postJson($this->queryEndpoint, [
                    'query' => "Show me applicant $i",
                ]);
        }

        // The 31st request should be rate-limited
        $response->assertStatus(429);
    }

    // ═══════════════════════════════════════════════════════════════════
    // 6. RESPONSE STRUCTURE
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function returns_structured_data_with_sql_and_explanation(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'Find applicant Juan Dela Cruz',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'first_name',
                    'last_name',
                ],
            ],
            'sql',
            'explanation',
        ]);
        $response->assertSee('Juan');
        $response->assertSee('Dela Cruz');
    }

    // ═══════════════════════════════════════════════════════════════════
    // 7. ROLE-BASED ACCESS (only certain roles can query)
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function ai_assistant_requires_report_viewer_role_or_above(): void
    {
        $restrictedRoles = ['applicant'];

        foreach ($restrictedRoles as $role) {
            $user = User::factory()->create([
                'agency_id' => $this->agency->id,
                'user_type' => $role,
            ]);

            $response = $this->actingAs($user)
                ->postJson($this->queryEndpoint, [
                    'query' => 'List applicants',
                ]);

            $response->assertForbidden();
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // 8. SUPERVISED QUERIES ONLY — audit logging
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function ai_query_is_logged_in_activity_log(): void
    {
        $this->actingAs($this->admin)
            ->postJson($this->queryEndpoint, [
                'query' => 'Show me 5 most recent applicants',
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'action'       => 'ai_query',
        ]);
    }
}
