<?php

namespace Tests\Feature\Dashboard;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "LANDAS: DASHBOARD bug" (Cyd, 2026-11-05):
 * "Sometimes it displays the old design. I have to click other module before
 * it refreshes to the latest design."
 *
 * /dashboard renders the legacy, non-tenant-scoped dashboard. Login honoured a
 * stale `url.intended` pointing there, so agency users landed on the old screen
 * (0 applicants / 0 FRAs) until they navigated somewhere else. Agency accounts
 * are now bounced to their real dashboard; only super admins keep the legacy
 * view.
 */
class LegacyDashboardRedirectTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function agency_users_are_sent_to_the_agency_dashboard(): void
    {
        $agency = Agency::factory()->create(['name' => 'Gulf']);
        $user = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('agency.dashboard'));
    }

    #[Test]
    public function stale_intended_url_does_not_land_an_agency_user_on_the_legacy_dashboard(): void
    {
        $agency = Agency::factory()->create(['name' => 'Gulf']);
        $user = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'email' => 'gulf.admin@example.com',
            'password' => bcrypt('secret123'),
        ]);

        // Simulate the leftover from a previous session.
        $this->withSession(['url.intended' => route('dashboard')])
            ->post('/login', [
                'email' => 'gulf.admin@example.com',
                'password' => 'secret123',
            ])
            ->assertRedirect(route('dashboard'));

        // Follow the redirect: the legacy page bounces to the agency dashboard.
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('agency.dashboard'));

        $this->actingAs($user)
            ->get(route('agency.dashboard'))
            ->assertOk();
    }

    #[Test]
    public function super_admin_still_gets_the_legacy_dashboard(): void
    {
        $super = User::factory()->create([
            'agency_id' => null,
            'user_type' => 'super_admin',
        ]);

        $this->actingAs($super)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs('dashboard');
    }
}
