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
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Agent Reports > Payments must only list payments coming from the Expenses &
 * Payments module (the payment amount recorded on an expense transaction).
 * Receivable-module uploads used to leak into this tab (Cyd 2026-10-23).
 */
class AgentReportPaymentsSourceTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Branch $branch;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $this->account = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Partial',
        ]);
    }

    private function makeAgent(): Agent
    {
        return Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    private function makeApplicant(Agent $agent): Applicant
    {
        return Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $agent->id,
        ]);
    }

    private function makeExpensePayment(Agent $agent, float $amount, float $payment, string $particular, string $status = 'approved'): ExpenseRequestItem
    {
        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'reference_no' => (string) rand(100000, 999999),
            'date'         => now()->toDateString(),
            'status'       => $status,
            'branch_id'    => $this->branch->id,
        ]);

        return ExpenseRequestItem::create([
            'expense_request_id' => $request->id,
            'charge'             => 'agent',
            'agent_id'           => $agent->id,
            'applicant_id'       => $this->makeApplicant($agent)->id,
            'account_id'         => $this->account->id,
            'currency'           => 'PHP',
            'amount'             => $amount,
            'payment'            => $payment,
            'status'             => $status,
            'particular'         => $particular,
        ]);
    }

    private function makeReceivable(Agent $agent, float $amount, string $code): Receivable
    {
        return Receivable::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'agent_id'     => $agent->id,
            'applicant_id' => $this->makeApplicant($agent)->id,
            'code'         => $code,
            'date'         => now()->toDateString(),
            'status'       => 'received',
            'amount'       => $amount,
            'account'      => 'Placement Fee',
            'debit_account'=> 'Receivable',
            'type'         => 'Full Payment',
            'mode'         => 'GCash',
        ]);
    }

    #[Test]
    public function payments_tab_lists_expenses_payments(): void
    {
        $agent = $this->makeAgent();
        $this->makeExpensePayment($agent, 30000.00, 2000.00, 'ftw commission');

        $response = $this->actingAs($this->user)->get(route('agent_report.index', ['tab' => 'payments']));

        $response->assertOk()
            ->assertSee('ftw commission')
            ->assertSee('2,000.00')
            ->assertSee('30,000.00');

        $this->assertCount(1, $response->viewData('payments'));
    }

    #[Test]
    public function payments_tab_excludes_uploaded_receivables(): void
    {
        $agent = $this->makeAgent();
        $this->makeReceivable($agent, 7777.00, '000747');

        $response = $this->actingAs($this->user)->get(route('agent_report.index', ['tab' => 'payments']));

        $response->assertOk()
            ->assertDontSee('000747')
            ->assertDontSee('7,777.00');

        $this->assertCount(0, $response->viewData('payments'));
    }

    #[Test]
    public function payments_tab_ignores_transactions_without_payment(): void
    {
        $agent = $this->makeAgent();
        $this->makeExpensePayment($agent, 5000.00, 0.00, 'no payment here');

        $response = $this->actingAs($this->user)->get(route('agent_report.index', ['tab' => 'payments']));

        $response->assertOk()->assertDontSee('no payment here');
        $this->assertCount(0, $response->viewData('payments'));
    }

    #[Test]
    public function payments_tab_ignores_agent_deductions(): void
    {
        AgentDeduction::create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'date'      => now()->toDateString(),
            'account'   => AgentDeduction::ACCOUNT_PAID,
            'amount'    => 5555.00,
            'particular'=> 'approval generated paid row',
        ]);

        $response = $this->actingAs($this->user)->get(route('agent_report.index', ['tab' => 'payments']));

        $response->assertOk()->assertDontSee('approval generated paid row');
        $this->assertCount(0, $response->viewData('payments'));
    }

    #[Test]
    public function payments_tab_respects_agent_filter(): void
    {
        $agentA = $this->makeAgent();
        $agentB = $this->makeAgent();

        $this->makeExpensePayment($agentA, 10000.00, 1111.00, 'payment for agent a');
        $this->makeExpensePayment($agentB, 20000.00, 2222.00, 'payment for agent b');

        $response = $this->actingAs($this->user)->get(route('agent_report.index', [
            'tab'      => 'payments',
            'agent_id' => $agentA->id,
        ]));

        $response->assertOk()
            ->assertSee('payment for agent a')
            ->assertDontSee('payment for agent b');
    }
}
