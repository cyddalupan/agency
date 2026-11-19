<?php

namespace Tests\Feature\Accounting;

use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regression tests for: "accounting user type — the finance tab is not on the
 * sidebar list. it should be there."
 *
 * Root cause: the outer Finance sidebar gate required `!isBranchAccount()`,
 * so billing (Accounting) users bound to a branch (branch_id > 0) lost the
 * entire Finance section — even though every accounting route
 * (accounting.*, receivable.*, expense_request.*) already allows `billing`.
 *
 * Fix: billing users always see the Finance section; only admin/staff branch
 * accounts keep the trimmed sidebar.
 */
class BillingFinanceSidebarVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    private function renderSidebar(User $user): \Illuminate\Testing\TestResponse
    {
        // expense_request.index is billing-accessible and renders the sidebar.
        return $this->actingAs($user)->get(route('expense_request.index'));
    }

    #[Test]
    public function billing_user_without_branch_sees_finance_section(): void
    {
        $billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);

        $this->renderSidebar($billing)
            ->assertOk()
            ->assertSee('Accounting Dashboard', false)
            ->assertSee(route('accounting.dashboard'), false)
            ->assertSee(route('receivable.index'), false)
            ->assertSee(route('expense_request.index'), false);
    }

    #[Test]
    public function billing_user_with_branch_sees_finance_section(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $branch->id,
            'user_type' => 'billing',
        ]);

        $this->renderSidebar($billing)
            ->assertOk()
            ->assertSee('Accounting Dashboard', false)
            ->assertSee(route('accounting.dashboard'), false)
            ->assertSee(route('receivable.index'), false)
            ->assertSee(route('expense_request.index'), false);
    }

    #[Test]
    public function branch_staff_still_gets_trimmed_sidebar(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $branch->id,
            'user_type' => 'staff',
        ]);

        // staff is not allowed on expense_request; render a page they can access.
        // (Card 6afe4e3d, 2026-11-19) This used to assert the word 'Finance' was
        // absent. That word now names the money-operations group, which staff
        // DO get — they keep Receivable and Expenses and Payments (2026-09-28).
        // Assert the actual contract instead: the accounting family is hidden.
        $html = $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString(route('accounting.dashboard'), $html);
        $this->assertStringNotContainsString(route('general_ledger.index'), $html);
    }

    #[Test]
    public function billing_user_can_access_accounting_dashboard(): void
    {
        $billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);

        $this->actingAs($billing)
            ->get(route('accounting.dashboard'))
            ->assertOk();
    }
}
