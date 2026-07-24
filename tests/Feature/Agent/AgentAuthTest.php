<?php

namespace Tests\Feature\Agent;

use App\Models\Agent;
use App\Models\Agency;
use App\Models\Applicant;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AgentAuthTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
    }

    #[Test]
    public function agent_can_view_login_page(): void
    {
        $response = $this->get(route('agent.login'));

        $response->assertOk();
        $response->assertSee('Agent Login');
    }

    #[Test]
    public function agent_can_login_with_valid_credentials(): void
    {
        $agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'password'  => Hash::make('password123'),
        ]);

        $response = $this->post(route('agent.login.submit'), [
            'email'    => $agent->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('agent.dashboard'));
        $this->assertAuthenticatedAs($agent, 'agent');
    }

    #[Test]
    public function agent_cannot_login_with_invalid_password(): void
    {
        $agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'password'  => Hash::make('password123'),
        ]);

        $response = $this->post(route('agent.login.submit'), [
            'email'    => $agent->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest('agent');
    }

    #[Test]
    public function agent_can_view_dashboard(): void
    {
        $agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($agent, 'agent')
            ->get(route('agent.dashboard'));

        $response->assertOk();
        $response->assertViewIs('agent.dashboard');
    }

    #[Test]
    public function agent_dashboard_shows_their_referred_applicants(): void
    {
        $agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $referred = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $agent->id,
            'source'    => 'Referral',
            'first_name' => 'Referred',
            'last_name'  => 'One',
        ]);

        $other = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => null,
            'source'    => 'Walk-in',
        ]);

        $response = $this->actingAs($agent, 'agent')
            ->get(route('agent.dashboard'));

        $response->assertOk();
        $response->assertSee('Referred One');
        $response->assertDontSee($other->first_name);
    }

    #[Test]
    public function guest_cannot_access_agent_dashboard(): void
    {
        $response = $this->get(route('agent.dashboard'));

        $response->assertRedirect(route('agent.login'));
    }

    #[Test]
    public function agent_can_logout(): void
    {
        $agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($agent, 'agent')
            ->post(route('agent.logout'));

        $response->assertRedirect(route('agent.login'));
        $this->assertGuest('agent');
    }
}
