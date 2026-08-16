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
 *  2. Legacy "Expense Journal" / "expenses.*" CRUD -> deprecated, replaced by
 *     the Tab 2 Expense Request module (expense_request.*), labeled
 *     "Expenses & Payments" with "Save Request" action.
 *  3. Collection Module ("Collections" sidebar item) -> removed
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

    // ---------- Tab 2 module: "Expenses & Payments", not legacy "Expense Journal" ----------

    #[Test]
    public function expense_module_is_labeled_expenses_payments_not_expense_journal(): void
    {
        $this->actingAs($this->admin)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee('Expenses &amp; Payments', false)
            ->assertDontSee('Expense Journal');
    }

    // ---------- "Record Expense" -> "Save Request" ----------

    #[Test]
    public function expense_create_action_is_labeled_save_request(): void
    {
        $this->actingAs($this->admin)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->assertSee('Save Request')
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
