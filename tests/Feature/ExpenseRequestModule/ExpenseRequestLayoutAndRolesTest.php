<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regression tests for the Expense & Payments (Tab 2) layout + role visibility:
 *
 *  1. The index page must render well-formed (balanced) HTML so the daisyUI
 *     drawer stays intact — an extra </div> here silently hides the sidebar
 *     (reported on gulf.fixitautoservices.com/expense-request).
 *  2. The summary cards render inside a responsive grid (design parity).
 *  3. Accounting (billing) users can access the finance tab AND see the
 *     "Expenses and Payments" sidebar link (routes already allow billing;
 *     the sidebar link was admin/super_admin-only — inconsistent).
 */
class ExpenseRequestLayoutAndRolesTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private User $billing;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();

        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $this->billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);

        $this->staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
    }

    // ---------- Issue 3: index page HTML must be balanced (drawer intact) ----------

    #[Test]
    public function expense_request_index_renders_balanced_html(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('expense_request.index'));

        $response->assertOk();

        $html = $response->getContent();

        $openDivs = preg_match_all('/<div\b/', $html);
        $closeDivs = substr_count($html, '</div>');

        // Unbalanced HTML breaks the daisyUI drawer → sidebar disappears.
        $this->assertSame(
            0,
            $openDivs - $closeDivs,
            "Expected balanced <div> tags, got open={$openDivs} close={$closeDivs}. " .
            'An extra </div> in expense_request/index.blade.php hides the sidebar.'
        );
    }

    #[Test]
    public function expense_request_index_summary_cards_render_in_grid(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('expense_request.index'));

        $response->assertOk()
            ->assertSee('grid grid-cols-1 md:grid-cols-3', false)
            ->assertSee('PHP Total')
            ->assertSee('Received');
    }

    #[Test]
    public function expense_request_index_still_shows_sidebar_drawer(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('expense_request.index'));

        $response->assertOk()
            ->assertSee('drawer lg:drawer-open', false)
            ->assertSee('drawer-side', false)
            ->assertSee('Expenses and Payments', false);
    }

    // ---------- Issue 2: accounting (billing) users get the finance tab ----------

    #[Test]
    public function billing_user_can_access_expense_request_index(): void
    {
        $this->actingAs($this->billing)
            ->get(route('expense_request.index'))
            ->assertOk();
    }

    #[Test]
    public function billing_user_sees_expenses_and_payments_sidebar_link(): void
    {
        $response = $this->actingAs($this->billing)
            ->get(route('expense_request.index'));

        $response->assertOk()
            ->assertSee('Expenses and Payments', false)
            ->assertSee(route('expense_request.index'));
    }

    #[Test]
    public function staff_user_still_cannot_access_expense_request_index(): void
    {
        $this->actingAs($this->staff)
            ->get(route('expense_request.index'))
            ->assertForbidden();
    }

    // ---------- Issue 1: navbar label renamed ----------

    #[Test]
    public function sidebar_shows_backout_cancelled_repat_label(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('expense_request.index'));

        $response->assertOk()
            ->assertSee('Backout, Cancelled & Repat', false)
            ->assertDontSee('Withdrawn & Repat', false);
    }
}
