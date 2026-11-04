<?php

namespace Tests\Feature\Agent;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * CYD 2026-11-04 — Gulf only: delete every registered agent.
 */
class AgentDeleteAllTest extends TestCase
{
    use RefreshDatabase;

    private Agency $gulf;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gulf = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->admin = User::factory()->create([
            'agency_id' => $this->gulf->id,
            'user_type' => 'admin',
        ]);
    }

    private function deleteAll(array $payload = ['confirmation' => 'DELETE ALL'])
    {
        return $this->actingAs($this->admin)->post(route('agents.delete_all'), $payload);
    }

    #[Test]
    public function gulf_admin_can_delete_all_registered_agents(): void
    {
        $agents = Agent::factory()->count(3)->create(['agency_id' => $this->gulf->id]);
        $otherAgency = Agency::factory()->create(['subdomain' => 'csdi']);
        $foreign = Agent::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->deleteAll();

        $response->assertRedirect(route('agents.index'));
        $response->assertSessionHas('success', 'Deleted 3 registered agent(s).');

        foreach ($agents as $agent) {
            $this->assertDatabaseMissing('agents', ['id' => $agent->id]);
        }

        // Another tenant's agents are never touched.
        $this->assertDatabaseHas('agents', ['id' => $foreign->id]);

        $this->assertDatabaseHas('activity_logs', [
            'action'    => 'agents.delete_all',
            'agency_id' => $this->gulf->id,
        ]);
    }

    #[Test]
    public function non_gulf_agency_cannot_delete_all_agents(): void
    {
        $csdi = Agency::factory()->create(['subdomain' => 'csdi']);
        $csdiAdmin = User::factory()->create([
            'agency_id' => $csdi->id,
            'user_type' => 'admin',
        ]);
        $agent = Agent::factory()->create(['agency_id' => $csdi->id]);

        $response = $this->actingAs($csdiAdmin)
            ->post(route('agents.delete_all'), ['confirmation' => 'DELETE ALL']);

        $response->assertForbidden();
        $this->assertDatabaseHas('agents', ['id' => $agent->id]);
    }

    #[Test]
    public function non_admin_cannot_delete_all_agents(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->gulf->id,
            'user_type' => 'staff',
        ]);
        $agent = Agent::factory()->create(['agency_id' => $this->gulf->id]);

        $response = $this->actingAs($staff)
            ->post(route('agents.delete_all'), ['confirmation' => 'DELETE ALL']);

        $response->assertForbidden();
        $this->assertDatabaseHas('agents', ['id' => $agent->id]);
    }

    #[Test]
    public function a_wrong_confirmation_phrase_does_not_delete_anything(): void
    {
        $agent = Agent::factory()->create(['agency_id' => $this->gulf->id]);

        $response = $this->deleteAll(['confirmation' => 'delete all']);

        $response->assertSessionHasErrors('confirmation');
        $this->assertDatabaseHas('agents', ['id' => $agent->id]);
    }

    #[Test]
    public function deletion_is_refused_when_agents_are_still_referenced(): void
    {
        $agent = Agent::factory()->create(['agency_id' => $this->gulf->id]);
        Applicant::factory()->create([
            'agency_id' => $this->gulf->id,
            'agent_id'  => $agent->id,
        ]);

        $response = $this->deleteAll();

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('agents', ['id' => $agent->id]);
    }

    #[Test]
    public function the_delete_all_button_is_only_rendered_for_gulf(): void
    {
        Agent::factory()->count(2)->create(['agency_id' => $this->gulf->id]);
        $otherAgency = Agency::factory()->create(['subdomain' => 'csdi']);
        $csdiAdmin = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);

        $this->actingAs($this->admin)
            ->get(route('agents.index'))
            ->assertOk()
            ->assertSee('Delete all agents');

        $this->actingAs($csdiAdmin)
            ->get(route('agents.index'))
            ->assertOk()
            ->assertDontSee('Delete all agents');
    }
}
