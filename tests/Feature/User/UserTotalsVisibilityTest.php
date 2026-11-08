<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card 6aeeecf6ecf4af3d71f051c9 (Josh correction, 2026-11-08):
 * "Remove only the TOTAL statistics with the red X. don't remove the module
 * itself."
 *
 * Receivable and Expenses & Payments stay accessible to every user_type
 * (Rest of Account included). Only the money-total summary cards — and the
 * "Dashboard totals:" status selector that drives the expense card — are
 * hidden from users who are not Admin (super_admin / admin) or Accounting
 * (billing).
 */
class UserTotalsVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
    }

    private function user(string $type): User
    {
        return User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => $type,
        ]);
    }

    // ---------- Rest of Account: modules open, totals hidden ----------

    #[Test]
    public function staff_can_open_receivable_but_does_not_see_the_total_amount_card(): void
    {
        $response = $this->actingAs($this->user('staff'))
            ->get(route('receivable.index'));

        $response->assertOk();
        $response->assertDontSee('Total Amount');
        // The count cards stay visible.
        $response->assertSee('Transactions');
        $response->assertSee('Received');
        // The summary grid collapses to two columns when the total card is hidden.
        $response->assertSee('sm:grid-cols-2', false);
    }

    #[Test]
    public function staff_can_open_expenses_but_does_not_see_the_php_total_card(): void
    {
        $response = $this->actingAs($this->user('staff'))
            ->get(route('expense_request.index'));

        $response->assertOk();
        $response->assertDontSee('PHP Total');
        $response->assertDontSee('Dashboard totals:');
        // The count cards stay visible.
        $response->assertSee('Requests');
        $response->assertSee('Released');
        $response->assertSee('md:grid-cols-2', false);
    }

    // ---------- Accounting (billing) keeps the totals ----------

    #[Test]
    public function accounting_sees_the_totals_on_both_pages(): void
    {
        $billing = $this->user('billing');

        $this->actingAs($billing)
            ->get(route('receivable.index'))
            ->assertOk()
            ->assertSee('Total Amount')
            ->assertSee('sm:grid-cols-3', false);

        $this->actingAs($billing)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee('PHP Total')
            ->assertSee('Dashboard totals:')
            ->assertSee('md:grid-cols-3', false);
    }

    // ---------- Admin keeps the totals ----------

    #[Test]
    public function admin_sees_the_totals_on_both_pages(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->get(route('receivable.index'))
            ->assertOk()
            ->assertSee('Total Amount');

        $this->actingAs($admin)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee('PHP Total')
            ->assertSee('Dashboard totals:');
    }

    // ---------- Sidebar: both modules visible to staff again ----------

    #[Test]
    public function sidebar_shows_receivable_and_expenses_links_for_staff(): void
    {
        $response = $this->actingAs($this->user('staff'))
            ->get(route('agency.dashboard'));

        $response->assertOk();
        $response->assertSee(route('receivable.index'), false);
        $response->assertSee('Receivable', false);
        $response->assertSee('Expenses and Payments', false);
    }

    // ---------- Branch-scoped users: no 500 from the totals aggregate ----------

    #[Test]
    public function branch_locked_accounting_can_open_expenses_and_sees_totals(): void
    {
        // Regression (Josh 2026-11-08): the totals aggregate joins
        // expense_request_items, so an unqualified branch_id in the base scope
        // produced SQLSTATE 1052 and a 500 for every branch-scoped user.
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($billing)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee('PHP Total');
    }

    #[Test]
    public function branch_locked_staff_can_open_expenses_without_totals(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'operation',
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($staff)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertDontSee('PHP Total')
            ->assertSee('Requests');
    }

    // ---------- Guard: the access map still lets Rest of Account in ----------

    #[Test]
    public function rest_of_account_still_reaches_both_modules(): void
    {
        foreach (['receivable', 'expense_request'] as $module) {
            foreach (['staff', 'operation', 'processor'] as $type) {
                $this->assertTrue(
                    \App\Support\ModuleAccess::allows($type, $module, $this->agency->id),
                    "{$type} should still reach the '{$module}' module."
                );
            }
        }
    }
}
