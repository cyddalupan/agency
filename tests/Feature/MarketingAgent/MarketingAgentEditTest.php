<?php

namespace Tests\Feature\MarketingAgent;

use App\Models\Agency;
use App\Models\MarketingAgency;
use App\Models\MarketingAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarketingAgentEditTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private MarketingAgency $marketingAgency;
    private MarketingAgent $agent;

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
        $this->agent = MarketingAgent::factory()->create([
            'agency_id' => $this->agency->id,
            'marketing_agency_id' => $this->marketingAgency->id,
            'name' => 'Original Agent',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_edit(): void
    {
        $response = $this->get(route('marketing-agencies.marketing-agents.edit', [$this->marketingAgency, $this->agent]));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function edit_form_displays(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.marketing-agents.edit', [$this->marketingAgency, $this->agent]));

        $response->assertOk();
        $response->assertSee('Edit Marketing Agent');
        $response->assertSee('Original Agent');
    }

    #[Test]
    public function update_saves_changes(): void
    {
        $response = $this->actingAs($this->user)
            ->put(route('marketing-agencies.marketing-agents.update', [$this->marketingAgency, $this->agent]), [
                'name' => 'Updated Agent',
                'status' => 'inactive',
            ]);

        $response->assertRedirect(route('marketing-agencies.marketing-agents.index', $this->marketingAgency));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('marketing_agents', [
            'id' => $this->agent->id,
            'name' => 'Updated Agent',
            'status' => 'inactive',
        ]);
    }

    #[Test]
    public function update_validates_required_name(): void
    {
        $response = $this->actingAs($this->user)
            ->put(route('marketing-agencies.marketing-agents.update', [$this->marketingAgency, $this->agent]), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function destroy_deletes_agent(): void
    {
        $response = $this->actingAs($this->user)
            ->delete(route('marketing-agencies.marketing-agents.destroy', [$this->marketingAgency, $this->agent]));

        $response->assertRedirect(route('marketing-agencies.marketing-agents.index', $this->marketingAgency));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('marketing_agents', ['id' => $this->agent->id]);
    }
}
