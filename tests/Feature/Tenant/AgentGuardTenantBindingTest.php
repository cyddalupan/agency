<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b008b8d3ca32bfcb58ce970, hard constraint 1 — Agent extends
 * Authenticatable and is queried by the `agent` guard DURING authentication.
 *
 * What was measured, not assumed:
 *   - On the control plane (/agent/login on the main host) no tenant is bound,
 *     but the test client's host is not one of tenant_domains.main_hosts, so
 *     the control-plane path is exercised through the console branch of
 *     TenantScope. A production main-host login DOES bind no tenant, and the
 *     agent login page is a guest path there — see the first test's note.
 *   - On a real agency host, IdentifyAgency binds the host's agency BEFORE the
 *     guard's query, so the query is filtered by the host: an agent of another
 *     agency could pass the query and sign in. That is the tenant defect for
 *     this model, and AgentAuthController now refuses it.
 *
 * Mutation checks for these tests are in the handoff brief: removing
 * `use HasTenant` turns the scoping test red and the cross-host test red
 * again; removing the controller's agency check turns only the cross-host test
 * red.
 */
class AgentGuardTenantBindingTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Agency $otherAgency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->otherAgency = Agency::factory()->create(['name' => 'AAA Other', 'subdomain' => 'aaa-other']);
        $this->agency      = Agency::factory()->create(['name' => 'Zeta Agency', 'subdomain' => 'zeta-agency']);
    }

    private function agent(Agency $agency, array $overrides = []): Agent
    {
        return Agent::factory()->create(array_merge([
            'agency_id' => $agency->id,
            'password'  => Hash::make('password123'),
            'status'    => 'active',
        ], $overrides));
    }

    #[Test]
    public function agent_can_log_in_on_their_own_agency_host(): void
    {
        $agent = $this->agent($this->agency);

        $response = $this->post(
            'https://' . $this->agency->subdomain . '.fixitautoservices.com' . route('agent.login.submit', [], false),
            ['email' => $agent->email, 'password' => 'password123'],
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('agent.dashboard'));
        $this->assertAuthenticatedAs($agent, 'agent');
    }

    /**
     * The defect this card exists to close, on the model that authenticates:
     * an agent of agency B signing in on agency A's host. Before the fix the
     * guard's lookup was filtered by the HOST, so B's agent was found and
     * signed in with A's tenant bound.
     */
    #[Test]
    public function an_agent_cannot_log_in_on_another_agencys_host(): void
    {
        $agent = $this->agent($this->otherAgency);

        $response = $this->post(
            'https://' . $this->agency->subdomain . '.fixitautoservices.com' . route('agent.login.submit', [], false),
            ['email' => $agent->email, 'password' => 'password123'],
        );

        $response->assertSessionHasErrors('email');
        $this->assertGuest('agent');
    }

    /**
     * The scope is on the model, so a bound tenant cannot list or fetch
     * another agency's agents — including through the guard's own lookup,
     * which is what makes the host check above unnecessary on a clean host
     * and what closes the cross-agency listing.
     */
    #[Test]
    public function agent_queries_are_scoped_to_the_bound_agency(): void
    {
        $own   = $this->agent($this->agency, ['name' => 'Own Agent']);
        $other = $this->agent($this->otherAgency, ['name' => 'Other Agent']);

        app()->instance('tenant_agency', $this->agency);

        $names = Agent::query()->pluck('name')->all();

        $this->assertContains('Own Agent', $names);
        $this->assertNotContains('Other Agent', $names, 'Agent::query() returned another agency\'s agent');
        $this->assertNull(Agent::find($other->id), 'Agent::find() crossed the tenant boundary');
        $this->assertSame($own->id, Agent::find($own->id)?->id);
    }

    /**
     * Authentication on a tenant host is scoped but must still work: the
     * agent's own agency is the host's agency, so the query finds the row and
     * the dashboard renders with that tenant bound.
     */
    #[Test]
    public function agent_dashboard_works_and_binds_the_agents_agency(): void
    {
        $agent = $this->agent($this->agency);

        $this->actingAs($agent, 'agent');
        $this->app['auth']->shouldUse('web');

        $this->get(route('agent.dashboard'))
            ->assertOk()
            ->assertSee($agent->name);

        $this->assertTrue(app()->has('tenant_agency'), 'no tenant was bound for a logged-in agent');
        $this->assertSame($this->agency->id, app('tenant_agency')->id);
    }

    /**
     * Console is a trusted operator context (TenantScope's console branch), so
     * a command or seeder can authenticate an agent. This is the path that
     * would throw UnboundTenantException if the console exemption were lost.
     */
    #[Test]
    public function agent_guard_authenticates_in_a_console_context(): void
    {
        $agent = $this->agent($this->agency);
        $this->assertTrue(app()->runningInConsole(), 'PHPUnit must boot through the console kernel for this to mean anything');

        $this->assertTrue(
            auth('agent')->attempt(['email' => $agent->email, 'password' => 'password123']),
            'agent guard could not authenticate in a console context'
        );
        $this->assertSame($agent->id, auth('agent')->id());
    }

    /**
     * No escape hatch was registered for authentication: the guard's provider
     * is the plain Eloquent provider for the Agent model, so every guard query
     * goes through the same scope as the rest of the app.
     */
    #[Test]
    public function the_agent_guard_uses_the_plain_eloquent_provider(): void
    {
        $this->assertSame('eloquent', config('auth.providers.agents.driver'),
            'a custom agents provider driver was registered for authentication');
        $this->assertSame(Agent::class, config('auth.providers.agents.model'));
    }
}
