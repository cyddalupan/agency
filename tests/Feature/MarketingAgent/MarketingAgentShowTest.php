<?php

namespace Tests\Feature\MarketingAgent;

use App\Models\Agency;
use App\Models\MarketingAgency;
use App\Models\MarketingAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarketingAgentShowTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private MarketingAgency $marketingAgency;
    private MarketingAgent $marketingAgent;

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
            'name' => 'Prime Marketing',
        ]);
        $this->marketingAgent = MarketingAgent::factory()->create([
            'agency_id' => $this->agency->id,
            'marketing_agency_id' => $this->marketingAgency->id,
            'name' => 'John Agent',
            'email' => 'john@example.com',
            'contact' => '09170000001',
            'status' => 'active',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_view(): void
    {
        $response = $this->get(route('marketing-agencies.marketing-agents.show', [
            $this->marketingAgency,
            $this->marketingAgent,
        ]));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function show_displays_agent_details(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.marketing-agents.show', [
                $this->marketingAgency,
                $this->marketingAgent,
            ]));

        $response->assertOk();
        $response->assertSee('John Agent');
        $response->assertSee('john@example.com');
        $response->assertSee('Prime Marketing');
    }

    #[Test]
    public function show_shows_back_link(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.marketing-agents.show', [
                $this->marketingAgency,
                $this->marketingAgent,
            ]));

        $response->assertOk();
        $response->assertSee('Back');
    }

    #[Test]
    public function show_displays_edit_button(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.marketing-agents.show', [
                $this->marketingAgency,
                $this->marketingAgent,
            ]));

        $response->assertOk();
        $response->assertSee('Edit');
    }
}
