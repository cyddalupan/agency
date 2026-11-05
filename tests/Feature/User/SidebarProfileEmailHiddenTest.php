<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The sidebar profile footer shows the signed-in user's name only.
 * The email address used to be printed under it (card: "Side Profile Remove Email").
 */
class SidebarProfileEmailHiddenTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_profile_footer_shows_name_but_not_email(): void
    {
        $agency = Agency::factory()->create();

        $user = User::factory()->create([
            'agency_id' => $agency->id,
            'name'      => 'Sidebar Profile Probe',
            'email'     => 'sidebar.profile.probe@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Sidebar Profile Probe');
        $response->assertDontSee('sidebar.profile.probe@example.com');
    }
}
