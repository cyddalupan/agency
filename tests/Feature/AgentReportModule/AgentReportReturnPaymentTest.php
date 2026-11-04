<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-11-04 (card 129): Agent Report "Return Payment".
 *
 *  - new Return Payment tab: released expenses charged to an agent whose
 *    account is "Return"
 *  - Agent Ledger: Return Payment table below Receivables + Total Return Payment
 *  - Total Return Payment is added to the Agent's Balance
 */
class AgentReportReturnPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Branch $branch;
    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'name'      => 'RETURN AGENT',
        ]);
    }

    private function account(string $name): Account
    {
        return Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => $name,
        ]);
    }

    private function makeItem(Account $account, string $status, float $amount, string $particular): ExpenseRequestItem
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
            'account_id'         => $account->id,
            'currency'           => 'PHP',
            'amount'             => $amount,
            'payment'            => 0,
            'status'             => $status,
            'particular'         => $particular,
        ]);
    }

    #[Test]
    public function return_payment_tab_lists_released_return_expenses_only(): void
    {
        $return   = $this->account('Return');
        $cashAdv  = $this->account('Cash advance');

        $this->makeItem($return, ExpenseRequest::STATUS_RELEASED, 5000, 'over deduction return');
        // Not released -> not money returned yet.
        $this->makeItem($return, ExpenseRequest::STATUS_PENDING, 1200, 'pending return line');
        // Released but another account -> out of scope.
        $this->makeItem($cashAdv, ExpenseRequest::STATUS_RELEASED, 900, 'cash advance line');

        $response = $this->actingAs($this->user)->get(route('agent_report.index', [
            'tab' => 'return-payment',
        ]));

        $response->assertOk();
        $response->assertSee('Return Payment');
        $response->assertSee('over deduction return');
        $response->assertDontSee('pending return line');
        $response->assertDontSee('cash advance line');
    }

    #[Test]
    public function return_payment_tab_shows_the_total(): void
    {
        $return = $this->account('Return');

        $this->makeItem($return, ExpenseRequest::STATUS_RELEASED, 5000, 'first return');
        $this->makeItem($return, ExpenseRequest::STATUS_RELEASED, 2500, 'second return');

        $response = $this->actingAs($this->user)->get(route('agent_report.index', [
            'tab' => 'return-payment',
        ]));

        $response->assertOk();
        $response->assertSee('Total Return Payment');
        $response->assertSee('7,500.00');
    }

    #[Test]
    public function return_payment_tab_is_listed_on_every_tab_bar(): void
    {
        $response = $this->actingAs($this->user)->get(route('agent_report.index'));

        $response->assertOk();
        $response->assertSee('Return Payment');
        $response->assertSee(route('agent_report.index', ['tab' => 'return-payment']), false);
    }

    #[Test]
    public function agent_ledger_shows_return_payment_table_and_adds_it_to_the_balance(): void
    {
        $return = $this->account('Return');
        $this->makeItem($return, ExpenseRequest::STATUS_RELEASED, 5000, 'over deduction return');

        $response = $this->actingAs($this->user)
            ->get(route('agent_report.show', $this->agent->id));

        $response->assertOk();
        $response->assertSee('Return Payment');
        $response->assertSee('over deduction return');
        $response->assertSee('Total Return Payment');
        // Balance = 0 + 0 − 0 + 5000 − 0
        $response->assertSee('5,000.00');
    }

    #[Test]
    public function agent_ledger_report_tab_has_the_return_payment_column(): void
    {
        $return = $this->account('Return');
        $this->makeItem($return, ExpenseRequest::STATUS_RELEASED, 3000, 'return in report');

        $response = $this->actingAs($this->user)->get(route('agent_report.index', [
            'tab' => 'report',
        ]));

        $response->assertOk();
        $response->assertSee('Total Return Payment');
        $response->assertSee('3,000.00');
    }

    #[Test]
    public function printed_ledger_includes_the_return_payment_table(): void
    {
        $return = $this->account('Return');
        $this->makeItem($return, ExpenseRequest::STATUS_RELEASED, 5000, 'over deduction return');

        $response = $this->actingAs($this->user)
            ->get(route('agent_report.show.print', $this->agent->id));

        $response->assertOk();
        $response->assertSee('Return Payment');
        $response->assertSee('over deduction return');
    }
}
