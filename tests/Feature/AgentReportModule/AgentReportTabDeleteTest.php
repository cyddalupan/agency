<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\ExpenseRequestStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-11-01 (card 124): "Admin: able to delete transaction on both
 * Deduction and Payment tabs."
 *
 * Deductions are soft-deleted with who/why stamped on the row; deleting a
 * payment zeroes the payment amount on the expense item and writes an audit
 * note on the expense request.
 */
class AgentReportTabDeleteTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Branch $branch;
    private User $admin;
    private User $staff;
    private Agent $agent;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $this->agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'name'      => 'DELETE PROBE AGENT',
        ]);
        $this->account = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Partial',
        ]);
    }

    private function makeDeduction(float $amount = 500, string $particular = 'Probe deduction'): AgentDeduction
    {
        return AgentDeduction::create([
            'agency_id'  => $this->agency->id,
            'user_id'    => $this->admin->id,
            'agent_id'   => $this->agent->id,
            'date'       => now()->toDateString(),
            'account'    => AgentDeduction::ACCOUNT_DEDUCTION,
            'amount'     => $amount,
            'particular' => $particular,
        ]);
    }

    private function makePaymentItem(float $payment = 300, float $amount = 1000): ExpenseRequestItem
    {
        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'reference_no' => '900001',
            'date'         => now()->toDateString(),
            'status'       => ExpenseRequest::STATUS_APPROVED,
            'branch_id'    => $this->branch->id,
        ]);

        return ExpenseRequestItem::create([
            'expense_request_id' => $request->id,
            'charge'             => 'agent',
            'agent_id'           => $this->agent->id,
            'account_id'         => $this->account->id,
            'currency'           => 'PHP',
            'amount'             => $amount,
            'payment'            => $payment,
            'status'             => ExpenseRequest::STATUS_APPROVED,
            'particular'         => 'Probe payment row',
        ]);
    }

    // ---------- Deductions ----------

    #[Test]
    public function admin_can_delete_a_deduction_transaction(): void
    {
        $deduction = $this->makeDeduction();

        $response = $this->actingAs($this->admin)->delete(
            route('agent_report.deduction.destroy', $deduction),
            ['reason' => 'Duplicate entry posted twice']
        );

        $response->assertRedirect(route('agent_report.index', ['tab' => 'deductions']));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('agent_deductions', ['id' => $deduction->id]);
        $fresh = AgentDeduction::withTrashed()->find($deduction->id);
        $this->assertSame('Duplicate entry posted twice', $fresh->delete_reason);
        $this->assertSame($this->admin->id, (int) $fresh->deleted_by);
    }

    #[Test]
    public function deleted_deduction_disappears_from_the_tab_and_ledger(): void
    {
        $deduction = $this->makeDeduction(1500, 'VISIBLE BEFORE DELETE');

        $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'deductions']))
            ->assertOk()
            ->assertSee('VISIBLE BEFORE DELETE');

        $this->actingAs($this->admin)->delete(route('agent_report.deduction.destroy', $deduction), [
            'reason' => 'Wrong agent',
        ]);

        $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'deductions']))
            ->assertOk()
            ->assertDontSee('VISIBLE BEFORE DELETE');
    }

    #[Test]
    public function non_admin_cannot_delete_a_deduction(): void
    {
        $deduction = $this->makeDeduction();

        $this->actingAs($this->staff)
            ->delete(route('agent_report.deduction.destroy', $deduction), ['reason' => 'nope'])
            ->assertForbidden();

        $this->assertNotSoftDeleted('agent_deductions', ['id' => $deduction->id]);
    }

    #[Test]
    public function delete_requires_a_reason(): void
    {
        $deduction = $this->makeDeduction();

        $this->actingAs($this->admin)
            ->delete(route('agent_report.deduction.destroy', $deduction), [])
            ->assertSessionHasErrors('reason');

        $this->assertNotSoftDeleted('agent_deductions', ['id' => $deduction->id]);
    }

    #[Test]
    public function deduction_of_another_agency_is_not_found(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAgent = Agent::factory()->create(['agency_id' => $otherAgency->id]);
        $deduction = AgentDeduction::create([
            'agency_id'  => $otherAgency->id,
            'user_id'    => $this->admin->id,
            'agent_id'   => $otherAgent->id,
            'date'       => now()->toDateString(),
            'account'    => AgentDeduction::ACCOUNT_DEDUCTION,
            'amount'     => 100,
            'particular' => 'Other agency',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('agent_report.deduction.destroy', $deduction), ['reason' => 'x'])
            ->assertNotFound();

        $this->assertNotSoftDeleted('agent_deductions', ['id' => $deduction->id]);
    }

    // ---------- Payments ----------

    #[Test]
    public function admin_can_delete_a_payment_transaction(): void
    {
        $item = $this->makePaymentItem(300, 1000);

        $response = $this->actingAs($this->admin)->delete(
            route('agent_report.payment.destroy', $item),
            ['reason' => 'Payment posted to the wrong agent']
        );

        $response->assertRedirect(route('agent_report.index', ['tab' => 'payments']));
        $response->assertSessionHas('success');

        $item->refresh();
        $this->assertEquals(0, (float) $item->payment);
        // The expense transaction itself must survive.
        $this->assertEquals(1000, (float) $item->amount);
        $this->assertDatabaseHas('expense_requests', ['id' => $item->expense_request_id]);

        $history = ExpenseRequestStatusHistory::where('expense_request_id', $item->expense_request_id)
            ->where('note', 'like', '%Payment of 300.00 deleted by%')
            ->first();
        $this->assertNotNull($history, 'audit note was not written');
    }

    #[Test]
    public function deleted_payment_disappears_from_the_payments_tab(): void
    {
        $item = $this->makePaymentItem(250, 1000);

        $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'payments']))
            ->assertOk()
            ->assertSee('Probe payment row');

        $this->actingAs($this->admin)->delete(route('agent_report.payment.destroy', $item), [
            'reason' => 'Duplicate payment',
        ]);

        $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'payments']))
            ->assertOk()
            ->assertDontSee('Probe payment row');
    }

    #[Test]
    public function non_admin_cannot_delete_a_payment(): void
    {
        $item = $this->makePaymentItem(300, 1000);

        $this->actingAs($this->staff)
            ->delete(route('agent_report.payment.destroy', $item), ['reason' => 'nope'])
            ->assertForbidden();

        $this->assertEquals(300, (float) $item->fresh()->payment);
    }

    #[Test]
    public function deleting_a_row_with_no_payment_is_rejected(): void
    {
        $item = $this->makePaymentItem(0, 1000);

        $response = $this->actingAs($this->admin)
            ->delete(route('agent_report.payment.destroy', $item), ['reason' => 'x']);

        $response->assertRedirect(route('agent_report.index', ['tab' => 'payments']));
        $response->assertSessionHas('error');
        $this->assertEquals(0, (float) $item->fresh()->payment);
    }

    // ---------- UI gating ----------

    #[Test]
    public function delete_buttons_only_render_for_admin(): void
    {
        $this->makeDeduction();
        $this->makePaymentItem();

        $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'deductions']))
            ->assertOk()
            ->assertSee('txDeleteModal');

        // (Cyd 2026-11-09 "GULF: Agent Reports module") Staff no longer has the
        // module at all, so the page is forbidden rather than rendered without
        // the delete controls.
        $this->actingAs($this->staff)
            ->get(route('agent_report.index', ['tab' => 'deductions']))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'payments']))
            ->assertOk()
            ->assertSee('txDeleteModal');

        $this->actingAs($this->staff)
            ->get(route('agent_report.index', ['tab' => 'payments']))
            ->assertForbidden();
    }
}
