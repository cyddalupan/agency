<?php

namespace Tests\Feature\MarketingAgent;

use App\Models\Agency;
use App\Models\MarketingAgency;
use App\Models\MarketingAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarketingAgentDeleteTest extends TestCase
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
    public function unauthenticated_user_cannot_delete(): void
    {
        $response = $this->delete(route('marketing-agencies.marketing-agents.destroy', [
            $this->marketingAgency,
            $this->marketingAgent,
        ]));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_delete_marketing_agent(): void
    {
        $response = $this->actingAs($this->user)
            ->delete(route('marketing-agencies.marketing-agents.destroy', [
                $this->marketingAgency,
                $this->marketingAgent,
            ]));

        $response->assertRedirect(route('marketing-agencies.marketing-agents.index', $this->marketingAgency));
        $this->assertDatabaseMissing('marketing_agents', ['id' => $this->marketingAgent->id]);
    }

    #[Test]
    public function delete_returns_success_message(): void
    {
        $response = $this->actingAs($this->user)
            ->delete(route('marketing-agencies.marketing-agents.destroy', [
                $this->marketingAgency,
                $this->marketingAgent,
            ]));

        $response->assertRedirect(route('marketing-agencies.marketing-agents.index', $this->marketingAgency));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function delete_does_not_affect_other_agents(): void
    {
        $otherAgent = MarketingAgent::factory()->create([
            'agency_id' => $this->agency->id,
            'marketing_agency_id' => $this->marketingAgency->id,
            'name' => 'Other Agent',
            'email' => 'other@example.com',
        ]);

        $this->actingAs($this->user)
            ->delete(route('marketing-agencies.marketing-agents.destroy', [
                $this->marketingAgency,
                $this->marketingAgent,
            ]));

        $this->assertDatabaseHas('marketing_agents', ['id' => $otherAgent->id]);
        $this->assertDatabaseMissing('marketing_agents', ['id' => $this->marketingAgent->id]);
    }
}
