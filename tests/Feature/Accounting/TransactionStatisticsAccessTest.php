<?php

namespace Tests\Feature\Accounting;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "LANDAS: Transaction Statistics" (2026-10-08).
 *
 * Requirement: the Statistics page (Receivable + Expenses) must be shown ONLY
 * to Admin and Accounting users (both GULF and FINAS).
 *
 * Root cause: the sidebar gate was
 *   canAccessModule('accounting') && isPrivileged()
 * where isPrivileged() matches a hard-coded name list (MAE/EVELYN/ANGEL), so a
 * normal Admin/Accounting account (e.g. Cyd) never saw the Statistics link even
 * though the route itself already allowed them.
 *
 * Fix: gate purely on the module map — `accounting` is allowed for
 * super_admin/admin/billing and denied for everyone else, which matches the
 * module.access middleware that already guards `accounting.dashboard`.
 */
class TransactionStatisticsAccessTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    private function makeUser(string $type): User
    {
        return User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => $type,
        ]);
    }

    /** The Statistics sidebar link points at this route. */
    private function statsLink(): string
    {
        return route('accounting.dashboard');
    }

    #[Test]
    public function admin_sees_statistics_in_sidebar(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee($this->statsLink(), false);
    }

    #[Test]
    public function super_admin_sees_statistics_in_sidebar(): void
    {
        $super = $this->makeUser('super_admin');

        $this->actingAs($super)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee($this->statsLink(), false);
    }

    #[Test]
    public function accounting_billing_user_sees_statistics_in_sidebar(): void
    {
        $accounting = $this->makeUser('billing');

        $this->actingAs($accounting)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee($this->statsLink(), false);
    }

    #[Test]
    public function staff_does_not_see_statistics_in_sidebar(): void
    {
        $staff = $this->makeUser('staff');

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee($this->statsLink(), false);
    }

    #[Test]
    public function branch_does_not_see_statistics_in_sidebar(): void
    {
        $branch = $this->makeUser('branch');

        $this->actingAs($branch)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee($this->statsLink(), false);
    }

    #[Test]
    public function admin_can_open_statistics_page(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get($this->statsLink())
            ->assertOk();
    }

    #[Test]
    public function accounting_can_open_statistics_page(): void
    {
        $this->actingAs($this->makeUser('billing'))
            ->get($this->statsLink())
            ->assertOk();
    }

    #[Test]
    public function staff_cannot_open_statistics_page(): void
    {
        $this->actingAs($this->makeUser('staff'))
            ->get($this->statsLink())
            ->assertForbidden();
    }

    #[Test]
    public function encoder_cannot_open_statistics_page(): void
    {
        $this->actingAs($this->makeUser('encoder'))
            ->get($this->statsLink())
            ->assertForbidden();
    }
}
