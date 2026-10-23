<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agent;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-10-23 — "Expense - Multiple transaction".
 *
 * One expense request can hold several transactions (line items) and they are
 * approved one at a time:
 *  - the index list shows one checkbox per transaction, not one per request;
 *  - bulk status accepts item_ids[] and only flips the ticked transactions;
 *  - the request status rolls up from its transactions;
 *  - approving a single transaction pays only that transaction out.
 */
class ExpensePerTransactionStatusTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private Branch $branch;
    private Country $country;
    private Account $main;
    private Account $sub;
    private Agent $agent;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $this->country = Country::factory()->create();

        $this->main = Account::factory()->create(['agency_id' => $this->agency->id, 'charge_type' => 'agent']);
        $this->sub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $this->main->id,
            'charge_type' => 'agent',
        ]);
        $this->agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
        ]);
        $this->applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $this->agent->id,
        ]);
    }

    /** Create one request holding several transactions (line items). */
    private function createMultiTransactionRequest(array $amounts = [1000.00, 2000.00, 3000.00]): ExpenseRequest
    {
        $lines = [];
        foreach ($amounts as $i => $amount) {
            $lines[] = [
                'charge'          => 'agent',
                'sub_account_id'  => $this->sub->id,
                'agent_id'        => $this->agent->id,
                'applicant_id'    => $this->applicant->id,
                'country_id'      => $this->country->id,
                'currency'        => 'PHP',
                'amount'          => $amount,
                'main_account_id' => $this->main->id,
                'particular'      => 'Transaction ' . ($i + 1),
            ];
        }

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), [
                'branch_id' => $this->branch->id,
                'lines'     => $lines,
            ])
            ->assertRedirect();

        return ExpenseRequest::latest('id')->firstOrFail();
    }

    #[Test]
    public function new_transactions_start_pending(): void
    {
        $request = $this->createMultiTransactionRequest();

        $this->assertCount(3, $request->items);
        foreach ($request->items as $item) {
            $this->assertSame(ExpenseRequest::STATUS_PENDING, $item->status);
        }
        $this->assertSame(ExpenseRequest::STATUS_PENDING, $request->status);
    }

    #[Test]
    public function index_shows_one_checkbox_per_transaction(): void
    {
        $request = $this->createMultiTransactionRequest([1000.00, 2000.00]);

        $html = $this->actingAs($this->admin)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->getContent();

        foreach ($request->items as $item) {
            $this->assertStringContainsString('name="item_ids[]" value="' . $item->id . '"', $html);
        }
    }

    #[Test]
    public function approving_one_transaction_leaves_the_others_pending(): void
    {
        $request = $this->createMultiTransactionRequest();
        $items = $request->items()->orderBy('id')->get();

        $this->actingAs($this->admin)
            ->post(route('expense_request.bulk_status'), [
                'item_ids' => [$items[0]->id],
                'status'   => ExpenseRequest::STATUS_APPROVED,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(ExpenseRequest::STATUS_APPROVED, $items[0]->fresh()->status);
        $this->assertSame(ExpenseRequest::STATUS_PENDING, $items[1]->fresh()->status);
        $this->assertSame(ExpenseRequest::STATUS_PENDING, $items[2]->fresh()->status);

        // Mixed request still reads as work in progress.
        $this->assertSame(ExpenseRequest::STATUS_PENDING, $request->fresh()->status);

        // Only the approved transaction created a Paid entry.
        $this->assertDatabaseHas('agent_deductions', [
            'expense_request_item_id' => $items[0]->id,
        ]);
        $this->assertDatabaseMissing('agent_deductions', [
            'expense_request_item_id' => $items[1]->id,
        ]);
    }

    #[Test]
    public function approving_every_transaction_rolls_the_request_up_to_approved(): void
    {
        $request = $this->createMultiTransactionRequest([500.00, 700.00]);
        $items = $request->items()->orderBy('id')->get();

        $this->actingAs($this->admin)
            ->post(route('expense_request.bulk_status'), [
                'item_ids' => $items->pluck('id')->all(),
                'status'   => ExpenseRequest::STATUS_APPROVED,
            ])
            ->assertRedirect();

        foreach ($items as $item) {
            $this->assertSame(ExpenseRequest::STATUS_APPROVED, $item->fresh()->status);
        }
        $this->assertSame(ExpenseRequest::STATUS_APPROVED, $request->fresh()->status);

        // One history entry for the request, covering both transactions.
        $this->assertDatabaseHas('expense_request_histories', [
            'expense_request_id' => $request->id,
            'from_status'        => ExpenseRequest::STATUS_PENDING,
            'to_status'          => ExpenseRequest::STATUS_APPROVED,
        ]);
    }

    #[Test]
    public function cancelling_one_transaction_keeps_the_request_open_and_removes_its_paid_entry(): void
    {
        $request = $this->createMultiTransactionRequest([900.00, 800.00]);
        $items = $request->items()->orderBy('id')->get();

        // Approve both first.
        $this->actingAs($this->admin)->post(route('expense_request.bulk_status'), [
            'item_ids' => $items->pluck('id')->all(),
            'status'   => ExpenseRequest::STATUS_APPROVED,
        ]);
        $this->assertSame(2, \App\Models\AgentDeduction::whereNotNull('expense_request_item_id')->count());

        // Cancel only the first transaction.
        $this->actingAs($this->admin)->post(route('expense_request.bulk_status'), [
            'item_ids' => [$items[0]->id],
            'status'   => ExpenseRequest::STATUS_CANCELLED,
        ])->assertRedirect();

        $this->assertSame(ExpenseRequest::STATUS_CANCELLED, $items[0]->fresh()->status);
        $this->assertSame(ExpenseRequest::STATUS_APPROVED, $items[1]->fresh()->status);

        // Cancelled transaction no longer pays out; the other one still does.
        $this->assertDatabaseMissing('agent_deductions', ['expense_request_item_id' => $items[0]->id]);
        $this->assertDatabaseHas('agent_deductions', ['expense_request_item_id' => $items[1]->id]);

        // Request stays at the least-advanced open transaction.
        $this->assertSame(ExpenseRequest::STATUS_APPROVED, $request->fresh()->status);
    }

    #[Test]
    public function request_level_selection_still_updates_every_transaction(): void
    {
        $request = $this->createMultiTransactionRequest([100.00, 200.00]);

        $this->actingAs($this->admin)
            ->post(route('expense_request.bulk_status'), [
                'ids'    => [$request->id],
                'status' => ExpenseRequest::STATUS_FOR_RELEASING,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'expense request(s)'));

        $this->assertSame(ExpenseRequest::STATUS_FOR_RELEASING, $request->fresh()->status);
        foreach ($request->items as $item) {
            $this->assertSame(ExpenseRequest::STATUS_FOR_RELEASING, $item->fresh()->status);
        }
    }

    #[Test]
    public function another_agency_cannot_flip_a_transaction(): void
    {
        $request = $this->createMultiTransactionRequest([400.00]);
        $item = $request->items()->firstOrFail();

        $otherAgency = Agency::factory()->create();
        $outsider = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);

        $this->actingAs($outsider)
            ->post(route('expense_request.bulk_status'), [
                'item_ids' => [$item->id],
                'status'   => ExpenseRequest::STATUS_APPROVED,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(ExpenseRequest::STATUS_PENDING, $item->fresh()->status);
    }

    #[Test]
    public function review_page_lists_a_status_per_transaction(): void
    {
        $request = $this->createMultiTransactionRequest([600.00, 700.00]);
        $item = $request->items()->firstOrFail();
        ExpenseRequestItem::where('id', $item->id)->update(['status' => ExpenseRequest::STATUS_CANCELLED]);

        $this->actingAs($this->admin)
            ->get(route('expense_request.show', $request))
            ->assertOk()
            ->assertSee('Cancelled')
            ->assertSee('Pending');
    }
}
