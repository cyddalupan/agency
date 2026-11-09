<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-11-04 (card 130): "Agent Ledger - Payment did not reflect. Tried an
 * Expense transaction with Payment amount. Did not reflect on the ledger."
 *
 * The single-agent ledger used to read only AgentDeduction "Paid" rows (created
 * when a request is approved). A payment amount typed on an expense transaction
 * never appeared. It now also lists those payment amounts, without double
 * counting the approval rows (which already net the payment out).
 */
class AgentLedgerPaymentReflectsExpensePaymentsTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Branch $branch;
    private Agent $agent;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'name'      => 'PAYMENT AGENT',
        ]);
        $this->account = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Partial',
        ]);
    }

    private function makeExpense(float $amount, float $payment, string $status, string $particular = 'placement fee'): ExpenseRequestItem
    {
        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'reference_no' => (string) random_int(100000, 999999),
            'date'         => now()->toDateString(),
            'status'       => $status,
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
            'status'             => $status,
            'particular'         => $particular,
        ]);
    }

    #[Test]
    public function ledger_lists_a_payment_recorded_on_an_expense_transaction(): void
    {
        $this->makeExpense(10000, 4000, ExpenseRequest::STATUS_RELEASED);

        $response = $this->actingAs($this->user)->get(route('agent_report.show', $this->agent->id));

        $response->assertOk();
        $response->assertSee('Payment');
        $response->assertSee('4,000.00');
        $response->assertSee('placement fee');
    }

    #[Test]
    public function ledger_lists_payments_from_pending_transactions_too(): void
    {
        $this->makeExpense(10000, 2500, ExpenseRequest::STATUS_PENDING, 'pending payment line');

        $response = $this->actingAs($this->user)->get(route('agent_report.show', $this->agent->id));

        $response->assertOk();
        $response->assertSee('2,500.00');
        $response->assertSee('pending payment line');
    }

    #[Test]
    public function ledger_payment_total_and_balance_include_the_expense_payment(): void
    {
        $this->makeExpense(10000, 4000, ExpenseRequest::STATUS_RELEASED);

        $response = $this->actingAs($this->user)->get(route('agent_report.show', $this->agent->id));

        $response->assertOk();
        // Total Paid footer follows the balance card; balance = -4000 with no other rows.
        $response->assertSee('Total Paid');
        $response->assertSee('-4,000.00');
    }

    #[Test]
    public function approval_paid_row_and_expense_payment_do_not_double_count(): void
    {
        $item = $this->makeExpense(10000, 4000, ExpenseRequest::STATUS_APPROVED);

        // What syncPaidEntryForItem() creates on approval: net = 10000 - 4000.
        AgentDeduction::create([
            'agency_id'              => $this->agency->id,
            'user_id'                => $this->user->id,
            'agent_id'               => $this->agent->id,
            'expense_request_item_id' => $item->id,
            'date'                   => now()->toDateString(),
            'account'                => AgentDeduction::ACCOUNT_PAID,
            'amount'                 => 6000,
            'particular'             => 'Expense #' . $item->expenseRequest->reference_no,
        ]);

        $response = $this->actingAs($this->user)->get(route('agent_report.show', $this->agent->id));

        $response->assertOk();
        // 6000 (paid entry) + 4000 (payment on the transaction) = the full 10,000.
        $response->assertSee('10,000.00');
        $response->assertDontSee('16,000.00');
    }

    #[Test]
    public function summary_report_counts_the_expense_payment(): void
    {
        $this->makeExpense(10000, 4000, ExpenseRequest::STATUS_RELEASED);

        $response = $this->actingAs($this->user)->get(route('agent_report.index', ['tab' => 'report']));

        $response->assertOk();
        $response->assertSee('4,000.00');
        $response->assertSee('-4,000.00');
    }

    #[Test]
    public function printed_ledger_lists_the_expense_payment(): void
    {
        $this->makeExpense(10000, 4000, ExpenseRequest::STATUS_RELEASED);

        $response = $this->actingAs($this->user)
            ->get(route('agent_report.show.print', $this->agent->id));

        $response->assertOk();
        $response->assertSee('4,000.00');
        $response->assertSee('placement fee');
    }
}
