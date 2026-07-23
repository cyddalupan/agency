<?php

namespace Tests\Feature\Fra;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class FraNavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a test employer user with a consistent password.
     */
    protected function createEmployer(): User
    {
        return User::factory()->create([
            'email'     => 'employer@test.com',
            'username'  => 'employer_user',
            'password'  => Hash::make('password-123'),
            'user_type' => 'employer',
            'status'    => 'active',
        ]);
    }

    /**
     * Helper to log in as employer and return the authenticated user.
     */
    protected function loginAsEmployer(): User
    {
        $user = $this->createEmployer();
        $this->post(route('fra.login.post'), [
            'login'    => 'employer_user',
            'password' => 'password-123',
        ]);
        return $user;
    }

    // ─────────────────────────────────────────────────────
    //  1. Top bar: brand/portal URL text + user badge + sign-out
    // ─────────────────────────────────────────────────────

    #[Test]
    public function top_bar_shows_user_initials_and_name(): void
    {
        $user = $this->loginAsEmployer();

        $this->get(route('fra.dashboard'))
            ->assertSee(strtoupper(substr($user->name, 0, 1)))
            ->assertSee($user->name);
    }

    #[Test]
    public function top_bar_has_sign_out_button(): void
    {
        $this->loginAsEmployer();

        $this->get(route('fra.dashboard'))
            ->assertSee('Sign Out');
    }

    #[Test]
    public function top_bar_has_brand_text(): void
    {
        $this->loginAsEmployer();

        $this->get(route('fra.dashboard'))
            ->assertSee('FRA Portal');
    }

    // ─────────────────────────────────────────────────────
    //  2. Horizontal tab navigation bar (teal/cyan bg)
    // ─────────────────────────────────────────────────────

    #[Test]
    public function horizontal_tab_nav_bar_is_present(): void
    {
        $this->loginAsEmployer();

        $response = $this->get(route('fra.dashboard'));

        // Check for each required tab link
        $response->assertSee('Dashboard');
        $response->assertSee('Line Up');
        $response->assertSee('On Process');
        $response->assertSee('Cancelled');
        $response->assertSee('Account');
    }

    #[Test]
    public function nav_tabs_have_correct_route_urls(): void
    {
        $this->loginAsEmployer();

        $response = $this->get(route('fra.dashboard'));

        $response->assertSee(route('fra.dashboard'));
        $response->assertSee(route('fra.lineup'));
        $response->assertSee(route('fra.onprocess'));
        $response->assertSee(route('fra.cancelled'));
        $response->assertSee(route('fra.account'));
    }

    #[Test]
    public function active_tab_is_highlighted(): void
    {
        $this->loginAsEmployer();

        $response = $this->get(route('fra.dashboard'));
        $response->assertSee('Dashboard');
        // "Selected" tab should also be present per Trello checklist
        $response->assertSee('Selected');
    }

    #[Test]
    public function selected_tab_link_is_present(): void
    {
        $this->loginAsEmployer();

        $this->get(route('fra.dashboard'))
            ->assertSee(route('fra.selected'));
    }

    // ─────────────────────────────────────────────────────
    //  3. Tab pages return 200
    // ─────────────────────────────────────────────────────

    #[Test]
    public function lineup_page_returns_200(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.lineup'))->assertOk();
    }

    #[Test]
    public function selected_page_returns_200(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.selected'))->assertOk();
    }

    #[Test]
    public function onprocess_page_returns_200(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.onprocess'))->assertOk();
    }

    #[Test]
    public function cancelled_page_returns_200(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.cancelled'))->assertOk();
    }

    #[Test]
    public function account_page_returns_200(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.account'))->assertOk();
    }

    // ─────────────────────────────────────────────────────
    //  4. Tab page titles
    // ─────────────────────────────────────────────────────

    #[Test]
    public function dashboard_page_shows_title(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.dashboard'))->assertSee('Dashboard');
    }

    #[Test]
    public function lineup_page_shows_title(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.lineup'))->assertSee('Line Up');
    }

    #[Test]
    public function selected_page_shows_title(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.selected'))->assertSee('Selected');
    }

    #[Test]
    public function onprocess_page_shows_title(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.onprocess'))->assertSee('On Process');
    }

    #[Test]
    public function cancelled_page_shows_title(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.cancelled'))->assertSee('Cancelled');
    }

    #[Test]
    public function account_page_shows_title(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.account'))->assertSee('Account');
    }

    // ─────────────────────────────────────────────────────
    //  5. Unauthenticated access redirects to fra.login
    // ─────────────────────────────────────────────────────

    #[Test]
    public function unauthenticated_users_are_redirected_to_fra_login(): void
    {
        $this->get(route('fra.lineup'))->assertRedirect(route('fra.login'));
        $this->get(route('fra.selected'))->assertRedirect(route('fra.login'));
        $this->get(route('fra.onprocess'))->assertRedirect(route('fra.login'));
        $this->get(route('fra.cancelled'))->assertRedirect(route('fra.login'));
        $this->get(route('fra.account'))->assertRedirect(route('fra.login'));
    }

    // ─────────────────────────────────────────────────────
    //  6. Mobile hamburger menu button
    // ─────────────────────────────────────────────────────

    #[Test]
    public function mobile_hamburger_menu_button_is_present(): void
    {
        $this->loginAsEmployer();

        $this->get(route('fra.dashboard'))
            ->assertSee('sidebar-drawer');
    }

    // ─────────────────────────────────────────────────────
    //  7. Dynamic logo area in header
    // ─────────────────────────────────────────────────────

    #[Test]
    public function header_has_logo_area(): void
    {
        $this->loginAsEmployer();

        $this->get(route('fra.dashboard'))
            ->assertSee('logo');
    }

    // ─────────────────────────────────────────────────────
    //  8. Dashboard Tab — KPI Metric Cards
    // ─────────────────────────────────────────────────────

    #[Test]
    public function dashboard_page_title_is_present(): void
    {
        $this->loginAsEmployer();

        $this->get(route('fra.dashboard'))
            ->assertSeeInOrder([__('messages.dashboard'), __('messages.stat_selected'), __('messages.stat_on_process'), __('messages.stat_flight'), __('messages.stat_deployed')]);
    }

    #[Test]
    public function dashboard_has_four_kpi_cards(): void
    {
        $this->loginAsEmployer();

        $response = $this->get(route('fra.dashboard'));

        $response->assertSee(__('messages.stat_selected'));
        $response->assertSee(__('messages.stat_on_process'));
        $response->assertSee(__('messages.stat_flight'));
        $response->assertSee(__('messages.stat_deployed'));
    }

    #[Test]
    public function dashboard_kpi_cards_have_large_numbers(): void
    {
        $this->loginAsEmployer();

        $response = $this->get(route('fra.dashboard'));

        // Check for 32px bold numbers in KPI cards
        $response->assertSee('font-size:32px');
    }

    #[Test]
    public function dashboard_kpi_numbers_are_uppercase_labels(): void
    {
        $this->loginAsEmployer();

        $this->get(route('fra.dashboard'))
            ->assertSee(__('messages.stat_selected'))
            ->assertSee(__('messages.stat_on_process'))
            ->assertSee(__('messages.stat_flight'))
            ->assertSee(__('messages.stat_deployed'));
    }

    #[Test]
    public function dashboard_kpi_cards_are_in_a_horizontal_row(): void
    {
        $this->loginAsEmployer();

        // KPI container should have a flex layout
        $this->get(route('fra.dashboard'))
            ->assertSee('display:flex')
            ->assertSee('font-size:32px');
    }

    #[Test]
    public function dashboard_shows_selected_kpi_count(): void
    {
        $this->loginAsEmployer();

        $this->get(route('fra.dashboard'))
            ->assertSee(__('messages.stat_selected'));
    }

    #[Test]
    public function dashboard_shows_onprocess_kpi_count(): void
    {
        $this->loginAsEmployer();

        $this->get(route('fra.dashboard'))
            ->assertSee(__('messages.stat_on_process'));
    }

    #[Test]
    public function dashboard_shows_flight_kpi_count(): void
    {
        $this->loginAsEmployer();

        $this->get(route('fra.dashboard'))
            ->assertSee(__('messages.stat_flight'));
    }

    #[Test]
    public function dashboard_shows_deployed_kpi_count(): void
    {
        $this->loginAsEmployer();

        $this->get(route('fra.dashboard'))
            ->assertSee(__('messages.stat_deployed'));
    }
}
