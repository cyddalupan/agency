<?php

namespace Tests\Feature\MarketingAgent;

use App\Models\Agency;
use App\Models\MarketingAgency;
use App\Models\MarketingAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarketingAgentCreateTest extends TestCase
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
    public function unauthenticated_user_cannot_access_create(): void
    {
        $response = $this->get(route('marketing-agencies.marketing-agents.create', $this->marketingAgency));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function create_form_displays(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.marketing-agents.create', $this->marketingAgency));

        $response->assertOk();
        $response->assertSee('Add Marketing Agent');
    }

    #[Test]
    public function store_creates_agent(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('marketing-agencies.marketing-agents.store', $this->marketingAgency), [
                'name' => 'New Agent',
                'email' => 'agent@example.com',
                'contact' => '09178888888',
            ]);

        $response->assertRedirect(route('marketing-agencies.marketing-agents.index', $this->marketingAgency));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('marketing_agents', [
            'name' => 'New Agent',
            'marketing_agency_id' => $this->marketingAgency->id,
            'agency_id' => $this->agency->id,
        ]);
    }

    #[Test]
    public function store_validates_required_name(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('marketing-agencies.marketing-agents.store', $this->marketingAgency), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function store_auto_sets_agency_and_agency_ids(): void
    {
        $this->actingAs($this->user)
            ->post(route('marketing-agencies.marketing-agents.store', $this->marketingAgency), [
                'name' => 'Auto Agent',
            ]);

        $this->assertDatabaseHas('marketing_agents', [
            'name' => 'Auto Agent',
            'marketing_agency_id' => $this->marketingAgency->id,
            'agency_id' => $this->agency->id,
        ]);
    }
}
