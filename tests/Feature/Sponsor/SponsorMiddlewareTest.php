<?php

namespace Tests\Feature\Sponsor;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SponsorMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create([
            'status' => 'active',
        ]);
    }

    #[Test]
    public function non_sponsor_user_is_redirected_from_sponsor_routes(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('sponsor.dashboard'));

        $response->assertRedirect(route('sponsor.login'));
    }

    #[Test]
    public function unauthenticated_user_is_redirected_to_sponsor_login(): void
    {
        $response = $this->get(route('sponsor.dashboard'));

        $response->assertRedirect(route('sponsor.login'));
    }
}
