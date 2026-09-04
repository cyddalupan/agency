<?php

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestStatusHistory;
use App\Models\Receivable;
use App\Models\ReceivableHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for: "on both /receivable and /expense-request there is no
 * delete button. There should be a delete action and a reason why."
 *
 * Design decisions:
 *  - Soft delete (SoftDeletes) so the audit trail (history rows) survives
 *    and the deletion reason is stored on the history entry (`note`).
 *  - Delete is restricted to super_admin/admin, matching the existing
 *    status-change gate.
 *  - The reason is required — a delete without a reason is rejected.
 */
class ExpenseReceivableDeleteTest extends TestCase
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

    // ---------- Receivable ----------

    private function makeReceivable(string $status = Receivable::STATUS_PENDING): Receivable
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id]);

        return Receivable::create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->admin->id,
            'agent_id'  => $agent->id,
            'code'      => 'AR-' . fake()->unique()->numberBetween(1000, 9999),
            'date'      => now()->toDateString(),
            'status'    => $status,
            'amount'    => 1000.00,
            'account'   => 'Test',
        ]);
    }

    #[Test]
    public function admin_can_delete_receivable_with_reason(): void
    {
        $receivable = $this->makeReceivable();

        $response = $this->actingAs($this->admin)
            ->delete(route('receivable.destroy', $receivable), ['reason' => 'Duplicate entry']);

        $response->assertRedirect(route('receivable.index'));

        $this->assertSoftDeleted('receivables', ['id' => $receivable->id]);
        $this->assertDatabaseHas('receivable_histories', [
            'receivable_id' => $receivable->id,
            'user_id'       => $this->admin->id,
            'to_status'     => 'deleted',
            'note'          => 'Duplicate entry',
        ]);
    }

    #[Test]
    public function receivable_delete_requires_a_reason(): void
    {
        $receivable = $this->makeReceivable();

        $response = $this->actingAs($this->admin)
            ->delete(route('receivable.destroy', $receivable), []);

        $response->assertSessionHasErrors('reason');
        $this->assertDatabaseHas('receivables', ['id' => $receivable->id, 'deleted_at' => null]);
    }

    #[Test]
    public function billing_cannot_delete_receivable(): void
    {
        $receivable = $this->makeReceivable();

        $this->actingAs($this->billing)
            ->delete(route('receivable.destroy', $receivable), ['reason' => 'nope'])
            ->assertForbidden();

        $this->assertDatabaseHas('receivables', ['id' => $receivable->id, 'deleted_at' => null]);
    }

    #[Test]
    public function deleted_receivable_disappears_from_index(): void
    {
        $receivable = $this->makeReceivable();

        $this->actingAs($this->admin)
            ->delete(route('receivable.destroy', $receivable), ['reason' => 'Cleanup']);

        $this->actingAs($this->admin)->get(route('receivable.index'))
            ->assertOk()
            ->assertDontSee($receivable->code);
    }

    // ---------- Expense Request ----------

    private function makeExpenseRequest(string $status = ExpenseRequest::STATUS_PENDING): ExpenseRequest
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);

        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'reference_no' => (string) fake()->unique()->numberBetween(5000, 9000),
            'date'         => now()->toDateString(),
            'status'       => $status,
        ]);

        $request->items()->create([
            'account_id' => $account->id,
            'particular' => 'Test item',
            'charge'     => 'office',
            'currency'   => 'PHP',
            'amount'     => 1000.00,
        ]);

        return $request;
    }

    // History models exist and can record the note (guards the data shape).
    #[Test]
    public function history_models_store_note_for_delete_audit(): void
    {
        $receivable = $this->makeReceivable();
        $request = $this->makeExpenseRequest();

        ReceivableHistory::create([
            'agency_id'     => $this->agency->id,
            'receivable_id' => $receivable->id,
            'user_id'       => $this->admin->id,
            'from_status'   => Receivable::STATUS_PENDING,
            'to_status'     => 'deleted',
            'note'          => 'reason A',
        ]);

        ExpenseRequestStatusHistory::create([
            'agency_id'          => $this->agency->id,
            'expense_request_id' => $request->id,
            'user_id'            => $this->admin->id,
            'from_status'        => ExpenseRequest::STATUS_PENDING,
            'to_status'          => 'deleted',
            'note'               => 'reason B',
        ]);

        $this->assertDatabaseHas('receivable_histories', ['note' => 'reason A']);
        $this->assertDatabaseHas('expense_request_histories', ['note' => 'reason B']);
    }
}
