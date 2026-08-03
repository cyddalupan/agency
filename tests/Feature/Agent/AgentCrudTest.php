<?php

namespace Tests\Feature\Agent;

use App\Models\Agent;
use App\Models\Agency;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AgentCrudTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function admin_can_view_agents_list(): void
    {
        Agent::factory()->count(3)->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('agents.index'));

        $response->assertOk();
        $response->assertViewIs('agents.index');
    }

    #[Test]
    public function admin_can_see_create_agent_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('agents.create'));

        $response->assertOk();
        $response->assertViewIs('agents.create');
    }

    #[Test]
    public function create_agent_form_does_not_require_password_fields(): void
    {
        // Agents do not log in — the create form must not ask for a password.
        // Regression: previously the form had no password fields while the controller
        // required one, so creation always failed. Now both are removed.
        $response = $this->actingAs($this->admin)
            ->get(route('agents.create'));

        $response->assertOk();
        $response->assertDontSee('name="password"', false);
        $response->assertDontSee('name="password_confirmation"', false);
    }

    #[Test]
    public function agent_can_be_stored_without_password(): void
    {
        // Agents do not log in — creating one must not require a password.
        $response = $this->actingAs($this->admin)
            ->post(route('agents.store'), [
                'name'            => 'No Login Agent',
                'email'           => 'nologinagent@test.com',
                'contact'         => '09171234567',
                'commission_rate' => 10,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('agents.index'));

        $this->assertDatabaseHas('agents', [
            'name'            => 'No Login Agent',
            'email'           => 'nologinagent@test.com',
            'agency_id'       => $this->admin->agency_id,
            'password'        => null,
        ]);
    }

    #[Test]
    public function admin_can_store_new_agent(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('agents.store'), [
                'name'            => 'New Agent',
                'email'           => 'newagent@test.com',
                'contact'         => '09171234567',
                'password'        => 'secret123',
                'password_confirmation' => 'secret123',
                'commission_rate' => 10,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('agents.index'));

        $this->assertDatabaseHas('agents', [
            'name'            => 'New Agent',
            'email'           => 'newagent@test.com',
            'agency_id'       => $this->admin->agency_id,
        ]);
    }

    #[Test]
    public function admin_can_view_edit_agent_form(): void
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('agents.edit', $agent));

        $response->assertOk();
        $response->assertViewIs('agents.edit');
    }

    #[Test]
    public function admin_can_update_agent(): void
    {
        $agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Old Name',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('agents.update', $agent), [
                'name'            => 'Updated Name',
                'email'           => $agent->email,
                'commission_rate' => 15,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('agents.index'));

        $this->assertDatabaseHas('agents', [
            'id'              => $agent->id,
            'name'            => 'Updated Name',
            'commission_rate' => 15,
        ]);
    }

    #[Test]
    public function non_admin_cannot_access_agent_routes(): void
    {
        $response = $this->get(route('agents.index'));

        $response->assertRedirect(route('login'));
    }
}
