<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Agency;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-10-24 ("Transactions: Expense and Receivable 'Delete'"):
 * delete is available to Admin accounts only, is a soft delete, and keeps a
 * reason on the status history.
 */
class ExpenseRequestDeleteTest extends TestCase
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
            'name'      => 'Gulf Admin',
        ]);
        $this->billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
            'name'      => 'Accounting',
        ]);
    }

    private function makeRequest(array $overrides = []): ExpenseRequest
    {
        return ExpenseRequest::create(array_merge([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'reference_no' => 'REF-' . fake()->unique()->numberBetween(1000, 9999),
            'date'         => now()->toDateString(),
            'status'       => 'pending',
        ], $overrides));
    }

    #[Test]
    public function admin_can_delete_an_expense_transaction_with_a_reason(): void
    {
        $request = $this->makeRequest();

        $response = $this->actingAs($this->admin)->delete(route('expense_request.destroy', $request), [
            'reason' => 'Duplicate entry, wrong branch',
        ]);

        $response->assertRedirect(route('expense_request.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('expense_requests', ['id' => $request->id]);
        $this->assertDatabaseHas('expense_request_histories', [
            'expense_request_id' => $request->id,
            'to_status'          => 'deleted',
            'note'               => 'Duplicate entry, wrong branch',
        ]);
    }

    #[Test]
    public function deleted_transactions_disappear_from_the_list(): void
    {
        $request = $this->makeRequest();

        $this->actingAs($this->admin)->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee($request->reference_no);

        $this->actingAs($this->admin)->delete(route('expense_request.destroy', $request), ['reason' => 'test']);

        $this->actingAs($this->admin)->get(route('expense_request.index'))
            ->assertOk()
            ->assertDontSee($request->reference_no);
    }

    #[Test]
    public function accounting_and_staff_cannot_delete(): void
    {
        $request = $this->makeRequest();

        $this->actingAs($this->billing)
            ->delete(route('expense_request.destroy', $request), ['reason' => 'nope'])
            ->assertForbidden();

        $this->assertNotSoftDeleted('expense_requests', ['id' => $request->id]);
    }

    #[Test]
    public function a_reason_is_required(): void
    {
        $request = $this->makeRequest();

        $this->actingAs($this->admin)
            ->delete(route('expense_request.destroy', $request))
            ->assertSessionHasErrors('reason');

        $this->assertNotSoftDeleted('expense_requests', ['id' => $request->id]);
        $this->assertSame(0, ExpenseRequestStatusHistory::where('to_status', 'deleted')->count());
    }

    #[Test]
    public function admins_of_another_agency_get_a_404(): void
    {
        $request = $this->makeRequest();

        $otherAgency = Agency::factory()->create();
        $otherAdmin = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);

        $this->actingAs($otherAdmin)
            ->delete(route('expense_request.destroy', $request), ['reason' => 'cross tenant'])
            ->assertNotFound();

        $this->assertNotSoftDeleted('expense_requests', ['id' => $request->id]);
    }

    #[Test]
    public function only_admins_see_the_delete_button(): void
    {
        $this->makeRequest();

        $this->actingAs($this->admin)->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee('Delete');

        $this->actingAs($this->billing)->get(route('expense_request.index'))
            ->assertOk()
            ->assertDontSee('delete-request-');
    }
}
