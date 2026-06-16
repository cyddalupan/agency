<?php

namespace Tests\Feature\MarketingAgency;

use App\Models\Agency;
use App\Models\MarketingAgency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarketingAgencyIndexTest extends TestCase
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
    public function unauthenticated_user_cannot_access(): void
    {
        $response = $this->get(route('marketing-agencies.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_view_list(): void
    {
        MarketingAgency::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.index'));

        $response->assertOk();
        $response->assertSee('Marketing Agencies');
    }

    #[Test]
    public function list_shows_agency_name(): void
    {
        MarketingAgency::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Star Marketing',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.index'));

        $response->assertSee('Star Marketing');
    }

    #[Test]
    public function list_paginates(): void
    {
        MarketingAgency::factory()->count(25)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.index'));

        $response->assertOk();
    }

    #[Test]
    public function list_is_tenant_scoped(): void
    {
        $agency1 = Agency::factory()->create();
        $agency2 = Agency::factory()->create();

        MarketingAgency::factory()->create([
            'agency_id' => $agency1->id,
            'name' => 'Agency One Marketing',
        ]);
        MarketingAgency::factory()->create([
            'agency_id' => $agency2->id,
            'name' => 'Agency Two Marketing',
        ]);

        app()->instance('tenant_agency', $agency1);

        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.index'));

        $response->assertSee('Agency One Marketing');
        $response->assertDontSee('Agency Two Marketing');
    }
}
