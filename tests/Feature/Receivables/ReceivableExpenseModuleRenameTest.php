<?php

namespace Tests\Feature\Receivables;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Module naming/restructure under Finance (TDD):
 *  1. "Receivable & Payments"  -> renamed to "Receivable"
 *  2. "Expense Journal"        -> renamed to "Expenses and Payments"
 *  3. "Record Expense" button  -> renamed to "Create Request"
 *  4. Collection Module ("Collections" sidebar item) -> removed
 */
class ReceivableExpenseModuleRenameTest extends TestCase
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

    // ---------- "Receivable & Payments" -> "Receivable" ----------

    #[Test]
    public function receivable_is_labeled_receivable_not_receivable_and_payments(): void
    {
        $this->actingAs($this->admin)
            ->get(route('receivable.index'))
            ->assertOk()
            ->assertSee('Receivable')
            ->assertDontSee('Receivable &amp; Payments')
            ->assertDontSee('Receivable & Payments');
    }

    // ---------- "Expense Journal" -> "Expenses and Payments" ----------

    #[Test]
    public function expense_module_is_labeled_expenses_and_payments_not_expense_journal(): void
    {
        $this->actingAs($this->admin)
            ->get(route('expenses.index'))
            ->assertOk()
            ->assertSee('Expenses and Payments')
            ->assertDontSee('Expense Journal');
    }

    // ---------- "Record Expense" -> "Create Request" ----------

    #[Test]
    public function expense_create_action_is_labeled_create_request(): void
    {
        $this->actingAs($this->admin)
            ->get(route('expenses.index'))
            ->assertOk()
            ->assertSee('Create Request')
            ->assertDontSee('Record Expense');

        $this->actingAs($this->admin)
            ->get(route('expenses.create'))
            ->assertOk()
            ->assertSee('Create Request')
            ->assertDontSee('Record Expense');
    }

    // ---------- Collection Module removed ----------

    #[Test]
    public function collection_route_is_removed(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::getRoutes()->getByName('accounting.collections') === null
        );

        // The URL should not be registered.
        $this->assertFalse(
            \Illuminate\Support\Facades\Route::has('accounting.collections')
        );
    }

    #[Test]
    public function collections_link_is_not_in_sidebar(): void
    {
        $this->actingAs($this->admin)
            ->get(route('receivable.index'))
            ->assertOk()
            ->assertDontSee('/accounting/collections');

        $this->actingAs($this->admin)
            ->get(route('accounting.dashboard'))
            ->assertOk()
            ->assertDontSee('/accounting/collections');
    }
}
