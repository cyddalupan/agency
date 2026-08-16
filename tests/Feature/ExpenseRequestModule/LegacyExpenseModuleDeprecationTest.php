<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LEGACY DUP checklist item — the old ExpenseController CRUD (expenses.*)
 * overlapped Tab 2 (Expense Request). Resolution: deprecate/remove the legacy
 * module; the new expense_request module is the single entry point.
 */
class LegacyExpenseModuleDeprecationTest extends TestCase
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

    #[Test]
    public function legacy_expenses_routes_are_no_longer_registered(): void
    {
        $this->assertFalse(Route::has('expenses.index'));
        $this->assertFalse(Route::has('expenses.create'));
        $this->assertFalse(Route::has('expenses.store'));
        $this->assertFalse(Route::has('expenses.edit'));
        $this->assertFalse(Route::has('expenses.update'));
        $this->assertFalse(Route::has('expenses.destroy'));
    }

    #[Test]
    public function legacy_expenses_url_returns_404(): void
    {
        $this->actingAs($this->admin)->get('/expenses')->assertNotFound();
        $this->actingAs($this->admin)->get('/expenses/create')->assertNotFound();
    }

    #[Test]
    public function sidebar_no_longer_links_to_legacy_expenses(): void
    {
        $this->actingAs($this->admin)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertDontSee('/expenses', false);
    }

    #[Test]
    public function new_expense_request_module_is_the_single_entry_point(): void
    {
        $this->assertTrue(Route::has('expense_request.index'));
        $this->assertTrue(Route::has('expense_request.create'));
        $this->assertTrue(Route::has('expense_request.store'));
        $this->assertTrue(Route::has('expense_request.show'));
        $this->assertTrue(Route::has('expense_request.status'));

        $this->actingAs($this->admin)->get(route('expense_request.index'))->assertOk();
    }
}
