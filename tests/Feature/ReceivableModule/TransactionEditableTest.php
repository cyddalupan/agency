<?php

namespace Tests\Feature\ReceivableModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-11-01: "Admin and Accounting Access — Able to edit transaction on
 * both Receivable and Expenses module." Edit screens + update endpoints are
 * gated to Admin (super_admin / admin) and Accounting (billing).
 */
class TransactionEditableTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private User $accounting;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->accounting = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);
        $this->staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
    }

    private function makeAgent(): Agent
    {
        return Agent::factory()->create(['agency_id' => $this->agency->id]);
    }

    private function makeReceivable(Agent $agent): Receivable
    {
        return Receivable::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'agent_id'     => $agent->id,
            'applicant_id' => null,
            'code'         => '000777',
            'date'         => now()->toDateString(),
            'status'       => Receivable::STATUS_PENDING,
            'ref_ar'       => 'AR-777',
            'amount'       => 1500.00,
            'account'      => 'Placement Fee',
            'debit_account' => 'Receivable',
            'type'         => 'Full Payment',
            'mode'         => 'Cash',
            'particular'   => 'Original particular',
        ]);
    }

    private function makeExpenseRequest(Agent $agent, Account $account): ExpenseRequest
    {
        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'reference_no' => 'REF-9001',
            'date'         => now()->toDateString(),
            'status'       => ExpenseRequest::STATUS_PENDING,
            'notes'        => 'Original note',
        ]);

        ExpenseRequestItem::create([
            'expense_request_id' => $request->id,
            'charge'       => 'agent',
            'agent_id'     => $agent->id,
            'currency'     => 'PHP',
            'amount'       => 1000.00,
            'payment'      => 0,
            'status'       => ExpenseRequest::STATUS_PENDING,
            'account_id'   => $account->id,
            'particular'   => 'Original line',
        ]);

        return $request;
    }

    // ---------- Access ----------

    #[Test]
    public function admin_and_accounting_can_open_the_receivable_edit_screen(): void
    {
        $agent = $this->makeAgent();
        $receivable = $this->makeReceivable($agent);

        foreach ([$this->admin, $this->accounting] as $user) {
            $this->actingAs($user)
                ->get(route('receivable.edit', $receivable))
                ->assertOk()
                ->assertSee('Edit Receivable')
                ->assertSee('Original particular');
        }
    }

    #[Test]
    public function staff_cannot_open_the_receivable_edit_screen(): void
    {
        $agent = $this->makeAgent();
        $receivable = $this->makeReceivable($agent);

        $this->actingAs($this->staff)
            ->get(route('receivable.edit', $receivable))
            ->assertForbidden();
    }

    #[Test]
    public function staff_cannot_update_a_receivable(): void
    {
        $agent = $this->makeAgent();
        $receivable = $this->makeReceivable($agent);

        $this->actingAs($this->staff)
            ->put(route('receivable.update', $receivable), [
                'date'     => now()->toDateString(),
                'agent_id' => $agent->id,
                'amount'   => 999.00,
            ])
            ->assertForbidden();

        $this->assertSame('1500.00', (string) $receivable->fresh()->amount);
    }

    #[Test]
    public function staff_cannot_open_the_expense_edit_screen(): void
    {
        $agent = $this->makeAgent();
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);
        $request = $this->makeExpenseRequest($agent, $account);

        $this->actingAs($this->staff)
            ->get(route('expense_request.edit', $request))
            ->assertForbidden();
    }

    // ---------- Receivable update ----------

    #[Test]
    public function accounting_can_update_a_receivable(): void
    {
        $agent = $this->makeAgent();
        $receivable = $this->makeReceivable($agent);

        $this->actingAs($this->accounting)
            ->put(route('receivable.update', $receivable), [
                'date'          => now()->toDateString(),
                'ref_ar'        => 'AR-888',
                'agent_id'      => $agent->id,
                'amount'        => 2750.50,
                'account'       => 'Monthly Collection',
                'debit_account' => 'Dollar Request',
                'type'          => 'Partial',
                'mode'          => 'Cheque',
                'particular'    => 'Corrected particular',
            ])
            ->assertRedirect(route('receivable.show', $receivable));

        $fresh = $receivable->fresh();
        $this->assertSame('2750.50', (string) $fresh->amount);
        $this->assertSame('Corrected particular', $fresh->particular);
        $this->assertSame('AR-888', $fresh->ref_ar);
        // Code + status must not change on an edit.
        $this->assertSame('000777', $fresh->code);
        $this->assertSame(Receivable::STATUS_PENDING, $fresh->status);
        // The edit is logged on the history trail.
        $this->assertDatabaseHas('receivable_histories', [
            'receivable_id' => $receivable->id,
            'user_id'       => $this->accounting->id,
        ]);
    }

    #[Test]
    public function receivable_update_rejects_an_applicant_from_another_agent(): void
    {
        $agentA = $this->makeAgent();
        $agentB = $this->makeAgent();
        $receivable = $this->makeReceivable($agentA);
        $foreignApplicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $agentB->id,
        ]);

        $this->actingAs($this->admin)
            ->put(route('receivable.update', $receivable), [
                'date'         => now()->toDateString(),
                'agent_id'     => $agentA->id,
                'applicant_id' => $foreignApplicant->id,
                'amount'       => 100.00,
            ])
            ->assertSessionHasErrors('applicant_id');

        $this->assertNull($receivable->fresh()->applicant_id);
    }

    #[Test]
    public function admin_can_edit_a_legacy_receivable_that_has_no_agent(): void
    {
        $receivable = Receivable::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'agent_id'     => null,
            'applicant_id' => null,
            'code'         => '000888',
            'date'         => now()->toDateString(),
            'status'       => Receivable::STATUS_PENDING,
            'amount'       => 800.00,
            'account'      => 'Placement Fee',
            'particular'   => 'Legacy particular',
        ]);

        $this->actingAs($this->admin)
            ->put(route('receivable.update', $receivable), [
                'date'       => now()->toDateString(),
                'agent_id'   => '',
                'amount'     => 850.00,
                'particular' => 'Fixed legacy particular',
            ])
            ->assertRedirect(route('receivable.show', $receivable));

        $fresh = $receivable->fresh();
        $this->assertSame('Fixed legacy particular', $fresh->particular);
        $this->assertSame('850.00', (string) $fresh->amount);
        $this->assertNull($fresh->agent_id);
    }

    // ---------- Expense update ----------

    #[Test]
    public function accounting_can_edit_an_expense_request_header_and_lines(): void
    {
        $agent = $this->makeAgent();
        $main = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Agent Main',
            'charge_type' => 'agent',
        ]);
        $sub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $main->id,
            'name'        => 'Agent Sub',
            'charge_type' => 'agent',
        ]);
        $request = $this->makeExpenseRequest($agent, $sub->id ? $sub : $main);
        $line = $request->items()->first();

        $this->actingAs($this->accounting)
            ->get(route('expense_request.edit', $request))
            ->assertOk()
            ->assertSee('Edit Expense Request')
            ->assertSee('Original line');

        $this->actingAs($this->accounting)
            ->put(route('expense_request.update', $request), [
                'date'  => now()->toDateString(),
                'notes' => 'Corrected note',
                'lines' => [
                    [
                        'id'              => $line->id,
                        'charge'          => 'agent',
                        'sub_account_id'  => $sub->id,
                        'agent_id'        => $agent->id,
                        'currency'        => 'PHP',
                        'amount'          => 4321.00,
                        'payment'         => 100.00,
                        'particular'      => 'Corrected line',
                    ],
                ],
            ])
            ->assertRedirect(route('expense_request.show', $request));

        $this->assertSame('Corrected note', $request->fresh()->notes);
        $freshLine = $line->fresh();
        $this->assertSame('4321.00', (string) $freshLine->amount);
        $this->assertSame('Corrected line', $freshLine->particular);
        $this->assertSame('REF-9001', $request->fresh()->reference_no);
        $this->assertSame(1, $request->items()->count());
    }

    #[Test]
    public function expense_edit_can_add_and_remove_lines(): void
    {
        $agent = $this->makeAgent();
        $main = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Agent Main 2',
            'charge_type' => 'agent',
        ]);
        $sub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $main->id,
            'name'        => 'Agent Sub 2',
            'charge_type' => 'agent',
        ]);
        $request = $this->makeExpenseRequest($agent, $sub);
        $existing = $request->items()->first();

        // Replace the single line with two brand-new ones.
        $this->actingAs($this->admin)
            ->put(route('expense_request.update', $request), [
                'date'  => now()->toDateString(),
                'lines' => [
                    ['charge' => 'agent', 'main_account_id' => $main->id, 'sub_account_id' => $sub->id, 'agent_id' => $agent->id, 'currency' => 'PHP', 'amount' => 10.00],
                    ['charge' => 'agent', 'main_account_id' => $main->id, 'sub_account_id' => $sub->id, 'agent_id' => $agent->id, 'currency' => 'PHP', 'amount' => 20.00],
                ],
            ])
            ->assertRedirect(route('expense_request.show', $request));

        $this->assertSame(2, $request->items()->count());
        $this->assertNull(ExpenseRequestItem::find($existing->id));
        $this->assertSame('30.00', number_format((float) $request->items()->sum('amount'), 2, '.', ''));
    }

    #[Test]
    public function expense_edit_is_scoped_to_the_current_agency(): void
    {
        $agent = $this->makeAgent();
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);
        $request = $this->makeExpenseRequest($agent, $account);

        $otherAgency = Agency::factory()->create();
        $outsider = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);

        $this->actingAs($outsider)
            ->get(route('expense_request.edit', $request))
            ->assertNotFound();
    }
}
