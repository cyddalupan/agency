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
 * Cyd 2026-11-01 (card 123): Agent Report > Commission must display all
 * RELEASED expenses whose account is "Fit to work" or "Deployed".
 *
 * Before this, released rows appeared nowhere on the page: the Commission tab
 * only listed PENDING items, the Receivables tab also filtered on PENDING, and
 * the Report tab only aggregated the released amounts into one column.
 */
class AgentReportReleasedCommissionTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Branch $branch;
    private Agent $agent;

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
            'name'      => 'RELEASED AGENT',
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

    private function commissionPage()
    {
        return $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'commission']));
    }

    #[Test]
    public function commission_tab_lists_released_fit_to_work_and_deployed_expenses(): void
    {
        $fitToWork = $this->account('Fit to work');
        $deployed  = $this->account('Deployed');
        $other     = $this->account('Ticket local');

        $this->makeItem($fitToWork, ExpenseRequest::STATUS_RELEASED, 1500, 'FTW released line');
        $this->makeItem($deployed, ExpenseRequest::STATUS_RELEASED, 2500, 'DEPLOYED released line');
        // Not released -> must not show up in the released block.
        $this->makeItem($fitToWork, ExpenseRequest::STATUS_PENDING, 999, 'FTW still pending');
        // Released but on another account -> out of scope for this card.
        $this->makeItem($other, ExpenseRequest::STATUS_RELEASED, 777, 'TICKET released line');

        $response = $this->commissionPage();

        $response->assertOk();
        $response->assertSee('Released Expenses');
        $response->assertSee('Fit to work');
        $response->assertSee('Deployed');
        $response->assertSee('FTW released line');
        $response->assertSee('DEPLOYED released line');
        $response->assertDontSee('TICKET released line');
        // 1500 + 2500 — total rendered in the footer.
        $response->assertSee('4,000.00');
    }

    #[Test]
    public function released_block_shows_php_equivalent_for_usd_rows(): void
    {
        $fitToWork = $this->account('Fit to work');

        $item = $this->makeItem($fitToWork, ExpenseRequest::STATUS_RELEASED, 100, 'USD released line');
        $item->update(['currency' => 'USD']);

        $response = $this->commissionPage();
        $response->assertOk();
        $response->assertSee('USD released line');
        $response->assertSee('₱', false);
    }

    #[Test]
    public function agent_filter_scopes_the_released_block(): void
    {
        $fitToWork = $this->account('Fit to work');
        $this->makeItem($fitToWork, ExpenseRequest::STATUS_RELEASED, 1500, 'AGENT ONE released');

        $other = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'name'      => 'OTHER AGENT',
        ]);
        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'reference_no' => (string) random_int(100000, 999999),
            'date'         => now()->toDateString(),
            'status'       => ExpenseRequest::STATUS_RELEASED,
            'branch_id'    => $this->branch->id,
        ]);
        ExpenseRequestItem::create([
            'expense_request_id' => $request->id,
            'charge'             => 'agent',
            'agent_id'           => $other->id,
            'account_id'         => $fitToWork->id,
            'currency'           => 'PHP',
            'amount'             => 5000,
            'payment'            => 0,
            'status'             => ExpenseRequest::STATUS_RELEASED,
            'particular'         => 'AGENT TWO released',
        ]);

        $response = $this->actingAs($this->user)->get(route('agent_report.index', [
            'tab'      => 'commission',
            'agent_id' => $this->agent->id,
        ]));

        $response->assertOk();
        $response->assertSee('AGENT ONE released');
        $response->assertDontSee('AGENT TWO released');
    }
}
