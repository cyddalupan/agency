<?php

namespace Tests\Feature\CaseManagement;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CaseCrudTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);
    }

    // ─── AUTHENTICATION ──────────────────────────────────────────────

    #[Test]
    public function unauthenticated_request_gets_401(): void
    {
        $response = $this->getJson('/api/cases');
        $response->assertUnauthorized();
    }

    #[Test]
    public function wrong_api_key_gets_401(): void
    {
        $response = $this->withHeaders(['X-API-Key' => 'invalid-key'])
            ->getJson('/api/cases');
        $response->assertUnauthorized();
    }

    // ─── CREATE ──────────────────────────────────────────────────────

    #[Test]
    public function create_case_requires_title_and_applicant_id(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cases', []);

        $response->assertJsonValidationErrors(['title', 'applicant_id']);
    }

    #[Test]
    public function create_case_with_minimal_data(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Visa delay issue',
                'applicant_id' => $this->applicant->id,
            ]);

        $response->assertCreated();
        $response->assertJsonFragment(['title' => 'Visa delay issue']);
        $this->assertDatabaseHas('cases', [
            'title' => 'Visa delay issue',
            'applicant_id' => $this->applicant->id,
            'agency_id' => $this->agency->id,
        ]);
    }

    #[Test]
    public function create_case_with_all_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Contract dispute',
                'description' => 'Employer changed contract terms',
                'applicant_id' => $this->applicant->id,
                'status' => 'open',
                'priority' => 'high',
            ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'title' => 'Contract dispute',
            'description' => 'Employer changed contract terms',
            'status' => 'open',
            'priority' => 'high',
        ]);
    }

    #[Test]
    public function create_case_defaults_to_open_status(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Default status',
                'applicant_id' => $this->applicant->id,
            ]);

        $response->assertCreated();
        $response->assertJsonFragment(['status' => 'open']);
    }

    #[Test]
    public function create_case_defaults_to_normal_priority(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Default priority',
                'applicant_id' => $this->applicant->id,
            ]);

        $response->assertCreated();
        $response->assertJsonFragment(['priority' => 'normal']);
    }

    #[Test]
    public function create_case_scopes_to_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherApplicant = Applicant::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Cross-agency attempt',
                'applicant_id' => $otherApplicant->id,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('cases', [
            'title' => 'Cross-agency attempt',
            'agency_id' => $this->agency->id,
        ]);
    }

    // ─── READ / LIST ─────────────────────────────────────────────────

    #[Test]
    public function list_cases_returns_paginated_results(): void
    {
        // Create multiple cases via model (since write test handles creation endpoint)
        // We need to test through API — use seeds or direct POST
        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($this->user)
                ->postJson('/api/cases', [
                    'title' => "Case {$i}",
                    'applicant_id' => $this->applicant->id,
                ]);
        }

        $response = $this->actingAs($this->user)
            ->getJson('/api/cases');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
            'data' => [['id', 'title', 'status', 'priority', 'created_at']],
        ]);
    }

    #[Test]
    public function list_cases_only_shows_own_agency(): void
    {
        $otherAgency = Agency::factory()->create();

        // Create case in own agency
        $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Our case',
                'applicant_id' => $this->applicant->id,
            ]);

        // Create case in other agency (as admin of that agency)
        $otherUser = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);
        $otherApplicant = Applicant::factory()->create(['agency_id' => $otherAgency->id]);
        $this->actingAs($otherUser)
            ->postJson('/api/cases', [
                'title' => 'Their case',
                'applicant_id' => $otherApplicant->id,
            ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/cases');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => 'Our case']);
        $response->assertJsonMissing(['title' => 'Their case']);
    }

    #[Test]
    public function show_case_returns_full_details(): void
    {
        $createResponse = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Detailed case',
                'description' => 'Full details here',
                'applicant_id' => $this->applicant->id,
                'priority' => 'high',
            ]);

        $caseId = $createResponse->json('id');

        $response = $this->actingAs($this->user)
            ->getJson("/api/cases/{$caseId}");

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => 'Detailed case',
            'description' => 'Full details here',
            'priority' => 'high',
        ]);
        $response->assertJsonStructure([
            'id', 'title', 'description', 'applicant_id',
            'status', 'priority', 'created_at', 'updated_at',
        ]);
    }

    // ─── UPDATE ──────────────────────────────────────────────────────

    #[Test]
    public function update_case_title_and_description(): void
    {
        $createResponse = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Original title',
                'applicant_id' => $this->applicant->id,
            ]);

        $caseId = $createResponse->json('id');

        $response = $this->actingAs($this->user)
            ->putJson("/api/cases/{$caseId}", [
                'title' => 'Updated title',
                'description' => 'Updated description',
            ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => 'Updated title',
            'description' => 'Updated description',
        ]);
    }

    #[Test]
    public function update_case_priority(): void
    {
        $createResponse = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Priority case',
                'applicant_id' => $this->applicant->id,
            ]);

        $caseId = $createResponse->json('id');

        $response = $this->actingAs($this->user)
            ->putJson("/api/cases/{$caseId}", [
                'priority' => 'urgent',
            ]);

        $response->assertOk();
        $response->assertJsonFragment(['priority' => 'urgent']);
    }

    // ─── CLOSE ───────────────────────────────────────────────────────

    #[Test]
    public function close_case_changes_status_to_closed(): void
    {
        $createResponse = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Close me',
                'applicant_id' => $this->applicant->id,
            ]);

        $caseId = $createResponse->json('id');

        $response = $this->actingAs($this->user)
            ->putJson("/api/cases/{$caseId}", [
                'status' => 'closed',
            ]);

        $response->assertOk();
        $response->assertJsonFragment(['status' => 'closed']);
    }

    #[Test]
    public function reopen_closed_case(): void
    {
        $createResponse = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Reopen me',
                'applicant_id' => $this->applicant->id,
            ]);

        $caseId = $createResponse->json('id');

        $this->actingAs($this->user)
            ->putJson("/api/cases/{$caseId}", ['status' => 'closed']);

        $response = $this->actingAs($this->user)
            ->putJson("/api/cases/{$caseId}", ['status' => 'open']);

        $response->assertOk();
        $response->assertJsonFragment(['status' => 'open']);
    }

    // ─── DELETE ──────────────────────────────────────────────────────

    #[Test]
    public function delete_case_removes_it(): void
    {
        $createResponse = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Delete me',
                'applicant_id' => $this->applicant->id,
            ]);

        $caseId = $createResponse->json('id');

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/cases/{$caseId}");

        $response->assertOk();
        $this->assertSoftDeleted('cases', ['id' => $caseId]);
    }

    #[Test]
    public function cannot_access_case_from_other_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherUser = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);
        $otherApplicant = Applicant::factory()->create(['agency_id' => $otherAgency->id]);

        $createResponse = $this->actingAs($otherUser)
            ->postJson('/api/cases', [
                'title' => 'Their case',
                'applicant_id' => $otherApplicant->id,
            ]);

        $caseId = $createResponse->json('id');

        $response = $this->actingAs($this->user)
            ->getJson("/api/cases/{$caseId}");

        $response->assertForbidden();
    }

    // ─── CASE SEARCH ─────────────────────────────────────────────────

    #[Test]
    public function search_cases_by_title(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Visa application problem',
                'applicant_id' => $this->applicant->id,
            ]);

        $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Contract renewal',
                'applicant_id' => $this->applicant->id,
            ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/cases/search?q=visa');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Visa application problem']);
        $response->assertJsonMissing(['title' => 'Contract renewal']);
    }

    #[Test]
    public function search_cases_by_status(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Urgent case',
                'applicant_id' => $this->applicant->id,
            ]);

        $createResponse = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Closed case',
                'applicant_id' => $this->applicant->id,
            ]);

        $this->actingAs($this->user)
            ->putJson("/api/cases/{$createResponse->json('id')}", ['status' => 'closed']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/cases/search?status=closed');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Closed case']);
        $response->assertJsonMissing(['title' => 'Urgent case']);
    }

    #[Test]
    public function search_cases_by_applicant_name(): void
    {
        $applicant2 = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Juan case',
                'applicant_id' => $applicant2->id,
            ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/cases/search?q=Dela%20Cruz');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Juan case']);
    }

    // ─── VALIDATION ──────────────────────────────────────────────────

    #[Test]
    public function rejects_invalid_priority_value(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Bad priority',
                'applicant_id' => $this->applicant->id,
                'priority' => 'super-urgent',
            ]);

        $response->assertJsonValidationErrors(['priority']);
    }

    #[Test]
    public function rejects_invalid_status_value(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cases', [
                'title' => 'Bad status',
                'applicant_id' => $this->applicant->id,
                'status' => 'maybe',
            ]);

        $response->assertJsonValidationErrors(['status']);
    }
}
