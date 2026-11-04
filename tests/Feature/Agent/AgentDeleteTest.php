<?php

namespace Tests\Feature\Agent;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * CYD 2026-11-04 card — "Agent module delete": admin only, one delete button
 * per row (previously only the bulk "Delete all agents" Gulf action existed).
 *
 * Rules enforced by AgentController::destroy():
 *  - administrator (admin / super_admin) only;
 *  - tenant-scoped: an admin can never delete another agency's agent;
 *  - history-safe: the agent is hard-deleted only when nothing references it,
 *    otherwise the delete is refused with a message telling the admin to
 *    deactivate instead.
 */
class AgentDeleteTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'csdi']);
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function makeAgent(array $overrides = []): Agent
    {
        return Agent::factory()->create(array_merge([
            'agency_id' => $this->agency->id,
            'name'      => 'Delete Me Agent',
        ], $overrides));
    }

    #[Test]
    public function admin_can_delete_an_unreferenced_agent(): void
    {
        $agent = $this->makeAgent();

        $response = $this->actingAs($this->admin)
            ->delete(route('agents.destroy', $agent));

        $response->assertRedirect(route('agents.index'));
        $response->assertSessionHas('success', 'Agent deleted successfully.');
        $this->assertDatabaseMissing('agents', ['id' => $agent->id]);
    }

    #[Test]
    public function the_delete_is_written_to_the_activity_log(): void
    {
        $agent = $this->makeAgent(['name' => 'Logged Delete Agent']);

        $this->actingAs($this->admin)->delete(route('agents.destroy', $agent));

        $this->assertDatabaseHas('activity_logs', [
            'action'      => 'agents.delete',
            'agency_id'   => $this->agency->id,
            'description' => 'Deleted agent Logged Delete Agent.',
        ]);
    }

    #[Test]
    public function non_admins_cannot_delete_an_agent(): void
    {
        $agent = $this->makeAgent();

        foreach (['staff', 'billing', 'accounting'] as $type) {
            $user = User::factory()->create([
                'agency_id' => $this->agency->id,
                'user_type' => $type,
            ]);

            $this->actingAs($user)
                ->delete(route('agents.destroy', $agent))
                ->assertForbidden();

            $this->assertDatabaseHas('agents', ['id' => $agent->id]);
        }
    }

    #[Test]
    public function an_admin_cannot_delete_another_agencys_agent(): void
    {
        $foreignAgency = Agency::factory()->create(['subdomain' => 'other']);
        $foreign = Agent::factory()->create([
            'agency_id' => $foreignAgency->id,
            'name'      => 'Foreign Agent',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('agents.destroy', $foreign))
            ->assertNotFound();

        $this->assertDatabaseHas('agents', ['id' => $foreign->id]);
    }

    #[Test]
    public function an_agent_with_references_is_refused_and_kept(): void
    {
        $agent = $this->makeAgent();
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $agent->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->from(route('agents.index'))
            ->delete(route('agents.destroy', $agent));

        $response->assertRedirect(route('agents.index'));
        $response->assertSessionHas('error', function ($message) {
            return str_contains($message, 'Cannot delete Delete Me Agent')
                && str_contains($message, '1 applicant(s)')
                && str_contains($message, 'Inactive');
        });

        $this->assertDatabaseHas('agents', ['id' => $agent->id]);
    }

    #[Test]
    public function the_index_shows_the_delete_button_to_admins_only(): void
    {
        $agent = $this->makeAgent(['name' => 'Row Button Agent']);

        $this->actingAs($this->admin)
            ->get(route('agents.index'))
            ->assertOk()
            ->assertSee('delete-agent-' . $agent->id, false)
            ->assertSee(route('agents.destroy', $agent), false);
    }

    #[Test]
    public function the_delete_button_carries_the_row_scoped_route_and_csrf_protection(): void
    {
        $agent = $this->makeAgent(['name' => 'Scoped Button Agent']);

        $html = $this->actingAs($this->admin)
            ->get(route('agents.index'))
            ->assertOk()
            ->getContent();

        // The dialog posts to the agent's own DELETE route and carries the method spoof.
        $this->assertStringContainsString('action="' . route('agents.destroy', $agent) . '"', $html);
        $this->assertStringContainsString('name="_method" value="DELETE"', $html);
        $this->assertStringContainsString('Delete Scoped Button Agent?', $html);
    }
}
