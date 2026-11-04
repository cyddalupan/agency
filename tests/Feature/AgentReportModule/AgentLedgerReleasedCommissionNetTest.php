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
 * Cyd 2026-11-04 (card 131): Agent Ledger > Released Commission must display
 * the NET amount (amount − payment), not the gross amount.
 *
 * Sample from the card: a released line of 30 with 5 already paid must read
 * 25.00 and the Total Commission must add up the nets.
 */
class AgentLedgerReleasedCommissionNetTest extends TestCase
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
            'name'      => 'NET COMMISSION AGENT',
        ]);
    }

    private function account(string $name): Account
    {
        return Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => $name,
        ]);
    }

    private function makeItem(Account $account, float $amount, float $payment, string $particular, string $currency = 'PHP'): ExpenseRequestItem
    {
        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'reference_no' => (string) random_int(100000, 999999),
            'date'         => now()->toDateString(),
            'status'       => ExpenseRequest::STATUS_RELEASED,
            'branch_id'    => $this->branch->id,
        ]);

        return ExpenseRequestItem::create([
            'expense_request_id' => $request->id,
            'charge'             => 'agent',
            'agent_id'           => $this->agent->id,
            'account_id'         => $account->id,
            'currency'           => $currency,
            'amount'             => $amount,
            'payment'            => $payment,
            'status'             => ExpenseRequest::STATUS_RELEASED,
            'particular'         => $particular,
        ]);
    }

    #[Test]
    public function net_amount_is_gross_minus_payment(): void
    {
        $item = $this->makeItem($this->account('Fit to work'), 30, 5, 'CARD SAMPLE');

        $this->assertSame(25.0, $item->netAmount());
    }

    #[Test]
    public function ledger_released_commission_displays_net_not_gross(): void
    {
        $this->makeItem($this->account('Fit to work'), 30, 5, 'CARD SAMPLE NET');

        $response = $this->actingAs($this->user)
            ->get(route('agent_report.show', $this->agent));

        $response->assertOk();
        $response->assertSee('CARD SAMPLE NET');
        $response->assertSee('Amount (Net)');
        // 30 gross − 5 payment → 25 displayed, gross 30 never shown as the amount.
        $response->assertSee('25.00');
        $response->assertDontSee('30.00');
    }

    #[Test]
    public function ledger_total_commission_adds_up_the_net_amounts(): void
    {
        $this->makeItem($this->account('Fit to work'), 30, 5, 'NET LINE ONE');   // 25
        $this->makeItem($this->account('Deployed'), 100, 0, 'NET LINE TWO');    // 100

        $response = $this->actingAs($this->user)
            ->get(route('agent_report.show', $this->agent));

        $response->assertOk();
        $response->assertSee('125.00');   // net total, not 130.00
        $response->assertDontSee('130.00');
    }

    #[Test]
    public function print_ledger_also_displays_net_amounts(): void
    {
        $this->makeItem($this->account('Fit to work'), 30, 5, 'PRINT NET LINE');

        $response = $this->actingAs($this->user)
            ->get(route('agent_report.show.print', $this->agent));

        $response->assertOk();
        $response->assertSee('25.00');
        $response->assertDontSee('30.00');
    }

    #[Test]
    public function agent_report_commission_column_matches_the_ledger_net(): void
    {
        $this->makeItem($this->account('Fit to work'), 30, 5, 'REPORT NET LINE');

        $response = $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'report']));

        $response->assertOk();
        $response->assertSee('25.00');
        $response->assertDontSee('30.00');
    }
}
