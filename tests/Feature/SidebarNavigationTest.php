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
        // This class navigates the admin sidebar: it opens the Settings page and
        // expects the settings link, which only exist for accounts the module
        // map treats as admin. Use a real privileged account (the shipped
        // MAE/EVELYN/ANGEL name list) rather than the default faker-named
        // `staff` user, which the module gate 403s.
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
