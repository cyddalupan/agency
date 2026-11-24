<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b056f91 (Cyd 2026-11-24): "Place 'Total amount' on both below the table
 * and before the headers 'Amount' so I don't have to scroll down to see the
 * total amount."
 *
 * Every tab total is now computed once in AgentReportController::index() as
 * $tabTotals and rendered by a sticky line above each table. These tests pin
 * the two things that can silently go wrong: the number is wrong (mixed
 * currency not converted, or the wrong collection summed) and the total does
 * not follow the agent filter.
 */
class AgentReportTabTotalsTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Branch $branch;
    private Agent $agentA;
    private Agent $agentB;
    private Account $cashAdvanceAccount;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::put('fx_usd_to_php', 56.0, 3600);

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        app()->instance('tenant_agency', $this->agency);

        $this->branch = Branch::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'TOTALS-BRANCH',
        ]);

        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => null,
            'user_type' => 'admin',
        ]);

        $this->agentA = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'name'      => 'TOTALS AGENT A',
        ]);
        $this->agentB = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'name'      => 'TOTALS AGENT B',
        ]);

        $this->cashAdvanceAccount = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Cash advance',
        ]);
    }

    private function makeCashAdvance(Agent $agent, float $amount, string $currency): ExpenseRequestItem
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
            'agent_id'           => $agent->id,
            'account_id'         => $this->cashAdvanceAccount->id,
            'currency'           => $currency,
            'amount'             => $amount,
            'payment'            => 0,
            'status'             => ExpenseRequest::STATUS_RELEASED,
            'particular'         => 'cash advance '.$currency,
        ]);
    }

    private function makeDeduction(Agent $agent, float $amount): AgentDeduction
    {
        return AgentDeduction::create([
            'agency_id'  => $this->agency->id,
            'user_id'    => $this->user->id,
            'agent_id'   => $agent->id,
            'date'       => now()->toDateString(),
            'account'    => AgentDeduction::ACCOUNT_DEDUCTION,
            'amount'     => $amount,
            'particular' => 'deduction row',
        ]);
    }

    private function amountFor(string $html, string $label): string
    {
        // The sticky line is "<label> ... ₱<amount>" inside one flex bar.
        $needle = preg_quote($label, '/');
        $this->assertSame(1, preg_match('/'.$needle.'.*?₱([\d,]+\.\d{2})/su', $html, $m),
            "expected exactly one '{$label}' line in the response");

        return $m[1];
    }

    #[Test]
    public function cash_advance_total_converts_usd_and_follows_the_agent_filter(): void
    {
        // Agent A: USD 100 -> PHP 5,600.00 (fx 56). Agent B: PHP 1,000.00.
        $this->makeCashAdvance($this->agentA, 100, 'USD');
        $this->makeCashAdvance($this->agentB, 1000, 'PHP');

        $html = $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'cash-advance']))
            ->assertOk()
            ->getContent();

        $this->assertSame('6,600.00', $this->amountFor($html, 'Total (all rows in this tab)'));

        $filtered = $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'cash-advance', 'agent_id' => $this->agentA->id]))
            ->assertOk()
            ->getContent();

        $this->assertSame('5,600.00', $this->amountFor($filtered, 'Total (all rows in this tab)'));
    }

    #[Test]
    public function deductions_total_is_the_same_number_in_the_sticky_line_and_the_footer(): void
    {
        $this->makeDeduction($this->agentA, 1200.50);
        $this->makeDeduction($this->agentB, 3000.00);

        $html = $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'deductions']))
            ->assertOk()
            ->getContent();

        $this->assertSame('4,200.50', $this->amountFor($html, 'Total (all rows in this tab)'));
        // The tfoot reads the same $tabTotals key; one rendered occurrence besides the line.
        $this->assertStringContainsString('₱4,200.50', $html);

        $filtered = $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'deductions', 'agent_id' => $this->agentA->id]))
            ->assertOk()
            ->getContent();

        $this->assertSame('1,200.50', $this->amountFor($filtered, 'Total (all rows in this tab)'));
    }

    #[Test]
    public function an_empty_tab_renders_no_total_line(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'cash-advance']))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('Total (all rows in this tab)', $html);
        $this->assertStringContainsString('No cash advance transactions yet.', $html);
    }

    #[Test]
    public function commission_tab_shows_the_commission_total_but_not_on_the_released_table(): void
    {
        $this->makeCashAdvance($this->agentA, 100, 'PHP'); // not commission — different account

        $html = $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'commission']))
            ->assertOk()
            ->getContent();

        // No pending commission rows -> no sticky line at all.
        $this->assertStringNotContainsString('Total (all rows in this tab)', $html);
        // The Released Expenses footer legend (when rows exist) is untouched.
        $this->assertStringContainsString('Released Expenses', $html);
    }
}
