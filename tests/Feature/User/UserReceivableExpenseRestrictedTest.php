<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\User;
use App\Support\ModuleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card 6aeeecf6ecf4af3d71f051c9 (Cyd, 2026-11-08):
 * "Receivable and Expense Payment module — remove this to all user except
 * Admin & Accounting user access."
 *
 * The two modules stay in ROUTE_MODULES (so the middleware resolves them) but
 * are removed from ModuleAccess::STAFF_ALLOWED and from every other non-admin
 * allowlist. Admin/super_admin keep full access; Accounting (`billing`) keeps
 * them because they are not in ACCOUNTING_DENIED.
 */
class UserReceivableExpenseRestrictedTest extends TestCase
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

    // ---------- Access map ----------

    #[Test]
    public function only_admin_and_accounting_can_access_receivable_and_expenses(): void
    {
        foreach (['receivable', 'expense_request'] as $module) {
            $this->assertTrue(ModuleAccess::allows('super_admin', $module, $this->agency->id));
            $this->assertTrue(ModuleAccess::allows('admin', $module, $this->agency->id));
            $this->assertTrue(ModuleAccess::allows('billing', $module, $this->agency->id));

            foreach ([
                'staff', 'operation', 'processor', 'encoder',
                'recruitment', 'branch', 'welfare', 'employer',
            ] as $type) {
                $this->assertFalse(
                    ModuleAccess::allows($type, $module, $this->agency->id),
                    "{$type} must NOT access the '{$module}' module."
                );
            }
        }
    }

    #[Test]
    public function rest_of_account_still_keeps_its_other_modules(): void
    {
        // Guard against the change accidentally trimming the whole allowlist.
        foreach (['dashboard', 'applicants', 'employers', 'reports', 'agent_report'] as $module) {
            $this->assertTrue(
                ModuleAccess::allows('staff', $module, $this->agency->id),
                "Rest of Account should still reach '{$module}'."
            );
        }
    }

    // ---------- Route enforcement ----------

    #[Test]
    public function staff_gets_403_on_both_modules(): void
    {
        $staff = $this->user('staff');

        $this->actingAs($staff)->get(route('receivable.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('expense_request.index'))->assertForbidden();
    }

    #[Test]
    public function operation_and_processor_are_blocked_too(): void
    {
        foreach (['operation', 'processor'] as $type) {
            $u = $this->user($type);

            $this->actingAs($u)->get(route('receivable.index'))->assertForbidden();
            $this->actingAs($u)->get(route('expense_request.index'))->assertForbidden();
        }
    }

    #[Test]
    public function accounting_and_admin_still_reach_both_modules(): void
    {
        foreach (['billing', 'admin'] as $type) {
            $u = $this->user($type);

            $this->actingAs($u)->get(route('receivable.index'))->assertOk();
            $this->actingAs($u)->get(route('expense_request.index'))->assertOk();
        }
    }

    // ---------- Sidebar ----------

    #[Test]
    public function sidebar_hides_both_links_for_staff(): void
    {
        $response = $this->actingAs($this->user('staff'))->get(route('agency.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Receivable</a>', false);
        $response->assertDontSee('Expenses and Payments', false);
    }

    #[Test]
    public function sidebar_shows_both_links_for_accounting(): void
    {
        $response = $this->actingAs($this->user('billing'))->get(route('agency.dashboard'));

        $response->assertOk();
        $response->assertSee('Receivable', false);
        $response->assertSee('Expenses and Payments', false);
    }
}
