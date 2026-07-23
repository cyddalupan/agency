<?php

namespace Tests\Feature\FRA;

use App\Models\User;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FraSessionTest extends TestCase
{
    use RefreshDatabase;

    protected Agency $agency;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create([
            'name' => 'Test Agency',
        ]);

        $this->user = User::factory()->create([
            'email' => 'employer@test.com',
            'password' => bcrypt('password123'),
            'user_type' => 'employer',
            'status' => 'active',
            'agency_id' => $this->agency->id,
        ]);
    }

    #[Test]
    public function authenticated_user_sees_user_badge_on_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('fra.dashboard'));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
    }

    #[Test]
    public function authenticated_user_sees_logout_button(): void
    {
        $response = $this->actingAs($this->user)->get(route('fra.dashboard'));

        $response->assertStatus(200);
        $response->assertSee(__('messages.sign_out'));
        $response->assertSee(route('fra.logout'));
    }

    #[Test]
    public function session_expires_after_logout(): void
    {
        $response = $this->actingAs($this->user)->post(route('fra.logout'));

        $response->assertRedirect(route('fra.login'));

        $this->assertGuest();
    }

    #[Test]
    public function dashboard_shows_user_avatar_initial(): void
    {
        $response = $this->actingAs($this->user)->get(route('fra.dashboard'));

        $response->assertStatus(200);
        // The layout usually shows the first letter of the name
        $initial = strtoupper(substr($this->user->name, 0, 1));
        $response->assertSee($initial);
    }
}
