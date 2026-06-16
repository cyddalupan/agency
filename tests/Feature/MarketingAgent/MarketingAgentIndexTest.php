<?php

namespace Tests\Feature\MarketingAgent;

use App\Models\Agency;
use App\Models\MarketingAgency;
use App\Models\MarketingAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarketingAgentIndexTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private MarketingAgency $marketingAgency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->marketingAgency = MarketingAgency::factory()->create([
            'agency_id' => $this->agency->id,
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access(): void
    {
        $response = $this->get(route('marketing-agencies.marketing-agents.index', $this->marketingAgency));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function index_displays_agents(): void
    {
        MarketingAgent::factory()->count(2)->create([
            'agency_id' => $this->agency->id,
            'marketing_agency_id' => $this->marketingAgency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.marketing-agents.index', $this->marketingAgency));

        $response->assertOk();
        $response->assertSee('Marketing Agents');
    }

    #[Test]
    public function index_shows_agent_name(): void
    {
        MarketingAgent::factory()->create([
            'agency_id' => $this->agency->id,
            'marketing_agency_id' => $this->marketingAgency->id,
            'name' => 'Agent Smith',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.marketing-agents.index', $this->marketingAgency));

        $response->assertSee('Agent Smith');
    }
}
