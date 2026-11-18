<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        // This class opens the Settings page, which the module map restricts to
        // admin/super_admin — the default faker-named `staff` user 403s there. The
        // privileged() state is not required by these routes; it is kept only
        // because that is how the admin sidebar is actually exercised in production.
        $this->user = User::factory()->privileged()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    public function test_settings_link_points_to_existing_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        // The sidebar should link to a settings page (not '#')
        $response->assertSee(route('settings.index'));
    }

    public function test_reports_link_points_to_existing_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        // The sidebar should link to a reports index page (not '#')
        $response->assertSee(route('reports.index'));
    }

    public function test_settings_page_is_accessible(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.index'));

        $response->assertOk();
        $response->assertSee('Settings');
    }

    public function test_reports_index_page_is_accessible(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Reports');
    }
}
