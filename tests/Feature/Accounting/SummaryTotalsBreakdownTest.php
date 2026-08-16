<?php

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\Agency;
use App\Models\ExpenseRequest;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regression tests for: "on page /expense-request PHP Total is the total
 * regardless if its expense of payment. same with page /receivable and I am
 * not sure if thats expected or should be fixed?"
 *
 * Decision: the combined totals stay (they are a useful gross figure), but
 * the summary cards must break the totals down by status (Pending vs
 * Received) — and on the expense page also by charge (Office vs Agent) —
 * so a single mixed number no longer masks what has actually been received.
 */
class SummaryTotalsBreakdownTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private User $billing;

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
    }

    private function makeExpenseRequest(string $status, array $items): ExpenseRequest
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);

        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'reference_no' => 'ER-' . fake()->unique()->numberBetween(1000, 9999),
            'date'         => now()->toDateString(),
            'status'       => $status,
        ]);

        foreach ($items as $item) {
            $request->items()->create(array_merge([
                'account_id' => $account->id,
                'particular' => 'Test item',
            ], $item));
        }

        return $request;
    }

    // ---------- Expense & Payments (Tab 2) ----------

    #[Test]
    public function expense_request_summary_splits_pending_and_received(): void
    {
        // Pending: office PHP 1000.00
        $this->makeExpenseRequest(ExpenseRequest::STATUS_PENDING, [
            ['charge' => 'office', 'currency' => 'PHP', 'amount' => 1000.00],
        ]);
        // Received: agent PHP 500.00
        $this->makeExpenseRequest(ExpenseRequest::STATUS_RECEIVED, [
            ['charge' => 'agent', 'currency' => 'PHP', 'amount' => 500.00],
        ]);

        $response = $this->actingAs($this->billing)->get(route('expense_request.index'));

        $response->assertOk()
            // Combined total still present (existing contract).
            ->assertSee('PHP Total')
            ->assertSee('₱1,500.00')
            // New: status breakdown so the mixed total is no longer ambiguous.
            ->assertSee('Pending')
            ->assertSee('₱1,000.00')
            ->assertSee('₱500.00');
    }

    #[Test]
    public function expense_request_summary_splits_office_and_agent(): void
    {
        $this->makeExpenseRequest(ExpenseRequest::STATUS_PENDING, [
            ['charge' => 'office', 'currency' => 'PHP', 'amount' => 700.00],
            ['charge' => 'agent', 'currency' => 'PHP', 'amount' => 300.00],
        ]);

        $response = $this->actingAs($this->billing)->get(route('expense_request.index'));

        $response->assertOk()
            ->assertSee('Office')
            ->assertSee('Agent')
            ->assertSee('₱700.00')
            ->assertSee('₱300.00');
    }

    #[Test]
    public function expense_request_summary_keeps_usd_breakdown(): void
    {
        $this->makeExpenseRequest(ExpenseRequest::STATUS_PENDING, [
            ['charge' => 'office', 'currency' => 'USD', 'amount' => 100.00],
        ]);

        $response = $this->actingAs($this->billing)->get(route('expense_request.index'));

        $response->assertOk()
            ->assertSee('PHP Total')
            ->assertSee('$100.00');
    }

    // ---------- Receivable (Tab 1) ----------

    #[Test]
    public function receivable_summary_splits_pending_and_received(): void
    {
        Receivable::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->admin->id,
            'status'    => Receivable::STATUS_PENDING,
            'amount'    => 2000.00,
        ]);
        Receivable::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->admin->id,
            'status'    => Receivable::STATUS_RECEIVED,
            'amount'    => 800.00,
        ]);

        $response = $this->actingAs($this->billing)->get(route('receivable.index'));

        $response->assertOk()
            // Combined total still present (existing contract).
            ->assertSee('Total Amount')
            ->assertSee('₱2,800.00')
            // New: status breakdown.
            ->assertSee('Pending')
            ->assertSee('₱2,000.00')
            ->assertSee('₱800.00');
    }
}
