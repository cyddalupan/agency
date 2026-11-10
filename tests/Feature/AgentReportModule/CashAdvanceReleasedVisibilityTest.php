<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6af17042 — "Cash Advance: Missing Transaction".
 *
 * Josh (2026-11-09): "All RELEASED status with CASH ADVANCE Account should also
 * be posted on the AGENTS REPORT > CASH ADVANCE Tab. currently there are 10
 * post only. there should be more than that."
 *
 * The tab listed PENDING rows only while `agentLedgerData()` has always summed
 * RELEASED cash advances into the agent balance — two answers for one ledger.
 */
class CashAdvanceReleasedVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->branch = Branch::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Manila',
        ]);
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        Cache::put('fx_usd_to_php', 56.0, 3600);
    }

    private function makeAgent(): Agent
    {
        return Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    private function makeCashAdvance(
        Agent $agent,
        float $amount,
        string $requestStatus,
        string $accountName = 'Cash advance',
        string $referenceNo = null
    ): ExpenseRequestItem {
        $er = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'reference_no' => $referenceNo ?? (string) random_int(100000, 999999),
            'date'         => now()->toDateString(),
            'status'       => $requestStatus,
            'branch_id'    => $this->branch->id,
            'notes'        => 'test',
        ]);

        $acct = Account::create([
            'agency_id'   => $this->agency->id,
            'name'        => $accountName,
            'type'        => 'expense',
            'charge_type' => 'agent',
            'parent_id'   => null,
        ]);

        $applicant = Applicant::factory()->withStatus(0)->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $agent->id,
        ]);

        return ExpenseRequestItem::create([
            'expense_request_id' => $er->id,
            'charge'             => 'agent',
            'agent_id'           => $agent->id,
            'applicant_id'       => $applicant->id,
            'country_id'         => Country::factory()->create()->id,
            'currency'           => 'PHP',
            'amount'             => $amount,
            'account_id'         => $acct->id,
            'particular'         => 'test',
        ]);
    }

    #[Test]
    public function released_cash_advance_appears_on_the_cash_advance_tab(): void
    {
        $agent = $this->makeAgent();
        $this->makeCashAdvance($agent, 3500.00, ExpenseRequest::STATUS_RELEASED, 'Cash advance', 'CA-REL-1');

        $response = $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'cash-advance']))
            ->assertOk();

        $response->assertSee('CA-REL-1');
        $response->assertSee('3,500.00');
    }

    #[Test]
    public function pending_and_released_are_both_listed(): void
    {
        $agent = $this->makeAgent();
        $this->makeCashAdvance($agent, 1000.00, ExpenseRequest::STATUS_PENDING, 'Cash advance', 'CA-PEND-1');
        $this->makeCashAdvance($agent, 2000.00, ExpenseRequest::STATUS_RELEASED, 'Cash advance', 'CA-REL-2');

        $response = $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'cash-advance']))
            ->assertOk();

        $response->assertSee('CA-PEND-1');
        $response->assertSee('CA-REL-2');
    }

    #[Test]
    public function cancelled_cash_advance_stays_hidden(): void
    {
        $agent = $this->makeAgent();
        $this->makeCashAdvance($agent, 9999.00, ExpenseRequest::STATUS_CANCELLED, 'Cash advance', 'CA-CANCEL-1');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'cash-advance']))
            ->assertOk()
            ->assertDontSee('CA-CANCEL-1');
    }

    #[Test]
    public function released_cash_advance_does_not_leak_into_commission_tab(): void
    {
        $agent = $this->makeAgent();
        $this->makeCashAdvance($agent, 5500.00, ExpenseRequest::STATUS_RELEASED, 'Cash advance', 'CA-REL-3');

        $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'commission']))
            ->assertOk()
            ->assertDontSee('CA-REL-3');
    }

    #[Test]
    public function pending_rows_are_ordered_before_released_rows(): void
    {
        $agent = $this->makeAgent();
        $this->makeCashAdvance($agent, 3000.00, ExpenseRequest::STATUS_PENDING, 'Cash advance', 'CA-ORDER-PEND');
        $this->makeCashAdvance($agent, 4000.00, ExpenseRequest::STATUS_RELEASED, 'Cash advance', 'CA-ORDER-REL');

        $html = $this->actingAs($this->user)
            ->get(route('agent_report.index', ['tab' => 'cash-advance']))
            ->assertOk()
            ->getContent();

        $pendingAt  = strpos($html, 'CA-ORDER-PEND');
        $releasedAt = strpos($html, 'CA-ORDER-REL');

        $this->assertNotFalse($pendingAt, 'Pending cash advance should be listed');
        $this->assertNotFalse($releasedAt, 'Released cash advance should be listed');
        $this->assertLessThan(
            $releasedAt,
            $pendingAt,
            'Pending cash advances must be ordered before released ones'
        );
    }
}
