<?php

namespace Tests\Feature\MarketingAgency;

use App\Models\Agency;
use App\Models\MarketingAgency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarketingAgencyShowTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_view(): void
    {
        $agency = MarketingAgency::factory()->create();
        $response = $this->get(route('marketing-agencies.show', $agency));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function show_displays_agency_details(): void
    {
        $marketingAgency = MarketingAgency::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Premium Partners Inc',
            'commission_rate' => 12.5,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.show', $marketingAgency));

        $response->assertOk();
        $response->assertSee('Premium Partners Inc');
        $response->assertSee('12.5%');
    }

    #[Test]
    public function show_shows_edit_button(): void
    {
        $marketingAgency = MarketingAgency::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.show', $marketingAgency));

        $response->assertSee('Edit');
    }

    #[Test]
    public function show_shows_back_link(): void
    {
        $marketingAgency = MarketingAgency::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.show', $marketingAgency));

        $response->assertSee('Back to Marketing Agencies');
    }
}


