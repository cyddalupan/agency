<?php

namespace Tests\Feature\ReferenceCrud;

use App\Models\Agency;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The "Chart of Accounts" module was renamed to just "Accounts" and moved
 * into the Settings area (no longer a top-level sidebar item).
 */
class AccountModuleSettingsTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    // ---------- Naming: "Chart of Accounts" -> "Accounts" ----------

    #[Test]
    public function accounts_homepage_is_labeled_accounts_not_chart_of_accounts(): void
    {
        Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Office Expenses',
            'type' => 'expense',
            'parent_id' => null,
        ]);

        $this->actingAs($this->admin)
            ->get(route('accounts.index'))
            ->assertOk()
            ->assertSee('Accounts')
            ->assertDontSee('Chart of Accounts');
    }

    #[Test]
    public function old_label_is_gone_from_create_and_edit_pages(): void
    {
        $this->actingAs($this->admin)
            ->get(route('accounts.create'))
            ->assertOk()
            ->assertDontSee('Chart of Accounts');

        $main = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Office Expenses',
            'type' => 'expense',
            'parent_id' => null,
        ]);
        $this->actingAs($this->admin)
            ->get(route('accounts.edit', $main))
            ->assertOk()
            ->assertDontSee('Chart of Accounts');
    }

    // ---------- Module lives under Settings ----------

    #[Test]
    public function accounts_routes_are_nested_under_settings_prefix(): void
    {
        $this->assertStringStartsWith('/settings', parse_url(route('accounts.index'), PHP_URL_PATH));
        $this->assertStringStartsWith('/settings', parse_url(route('accounts.create'), PHP_URL_PATH));
    }

    #[Test]
    public function settings_index_has_an_accounts_module_card(): void
    {
        $this->actingAs($this->admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Accounts')
            ->assertSee(route('accounts.index'));
    }

    #[Test]
    public function sidebar_no_longer_shows_chart_of_accounts_top_level_item(): void
    {
        // The top-level sidebar link is removed; the label "Chart of Accounts" must not render as a standalone nav item.
        $this->actingAs($this->admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertDontSee('Chart of Accounts');
    }

    // ---------- Access control preserved ----------

    #[Test]
    public function accounts_still_accessible_to_admin_only(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($staff)->get(route('accounts.index'))->assertForbidden(403);
        $this->actingAs($this->admin)->get(route('accounts.index'))->assertOk();
    }

    #[Test]
    public function settings_index_still_requires_auth(): void
    {
        $this->get(route('settings.index'))->assertRedirect(route('login'));
    }
}
