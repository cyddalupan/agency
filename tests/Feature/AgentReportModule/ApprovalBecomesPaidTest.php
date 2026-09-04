<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Account;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Approved expense requests become Paid entries in the agent report's
 * Deductions & Paid tab; requested tabs (Commission / Cash Advance /
 * Receivables) only show pending items (Toybits 2026-08-29).
 */
class ApprovalBecomesPaidTest extends TestCase
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
        $this->main = Account::create([
            'agency_id'   => $this->agency->id,
            'name'        => 'AGENT',
            'type'        => 'income',
            'charge_type' => 'agent',
        ]);
        $this->sub = Account::create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $this->main->id,
            'name'        => 'Placement Fee',
            'type'        => 'income',
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

        // Deterministic FX rate for USD->PHP conversion assertions.
        Cache::put('fx_usd_to_php', 56.0, 3600);
    }

    private function storeRequest(float $amount = 10000.00, float $payment = 4000.00, string $currency = 'PHP'): ExpenseRequest
    {
        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), [
                'date'      => now()->toDateString(),
                'branch_id' => $this->branch->id,
                'lines'     => [[
                    'charge'          => 'agent',
                    'sub_account_id'  => $this->sub->id,
                    'agent_id'        => $this->agent->id,
                    'applicant_id'    => $this->applicant->id,
                    'country_id'      => $this->country->id,
                    'currency'        => $currency,
                    'amount'          => $amount,
                    'payment'         => $payment,
                    'particular'      => 'Placement fee',
                ]],
            ])
            ->assertRedirect();

        return ExpenseRequest::latest('id')->firstOrFail();
    }

    private function approve(ExpenseRequest $request): void
    {
        $this->actingAs($this->admin)
            ->patch(route('expense_request.status', $request), ['status' => 'approved'])
            ->assertRedirect();
    }

    #[Test]
    public function approving_creates_a_paid_entry_with_the_net_amount(): void
    {
        $request = $this->storeRequest(10000.00, 4000.00);
        $this->approve($request);

        $this->assertDatabaseHas('agent_deductions', [
            'agency_id'              => $this->agency->id,
            'agent_id'               => $this->agent->id,
            'applicant_id'           => $this->applicant->id,
            'account'                => AgentDeduction::ACCOUNT_PAID,
            'amount'                 => 6000.00, // 10000 - 4000
            'expense_request_item_id' => $request->items->first()->id,
        ]);
    }

    #[Test]
    public function approving_converts_usd_items_to_php_for_the_paid_entry(): void
    {
        $request = $this->storeRequest(100.00, 0.00, 'USD');
        $this->approve($request);

        $this->assertDatabaseHas('agent_deductions', [
            'account' => AgentDeduction::ACCOUNT_PAID,
            'amount'  => 5600.00, // 100 USD * 56
        ]);
    }

    #[Test]
    public function approved_item_appears_in_deductions_tab_and_not_in_commission(): void
    {
        $request = $this->storeRequest(10000.00, 4000.00);
        $this->approve($request);

        $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'deductions']))
            ->assertOk()
            ->assertSee('Paid')
            ->assertSee('6,000.00')
            ->assertSee('Placement fee');

        $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'commission']))
            ->assertOk()
            ->assertDontSee('Placement fee');
    }

    #[Test]
    public function pending_item_still_appears_in_commission_tab(): void
    {
        $this->storeRequest(10000.00, 0.00);

        $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'commission']))
            ->assertOk()
            ->assertSee('Placement fee')
            ->assertSee('10,000.00');
    }

    #[Test]
    public function cancelling_removes_the_linked_paid_entry(): void
    {
        $request = $this->storeRequest(10000.00, 4000.00);
        $this->approve($request);

        $this->assertDatabaseHas('agent_deductions', [
            'expense_request_item_id' => $request->items->first()->id,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('expense_request.status', $request), ['status' => 'cancelled'])
            ->assertRedirect();

        $this->assertDatabaseMissing('agent_deductions', [
            'expense_request_item_id' => $request->items->first()->id,
        ]);
    }

    #[Test]
    public function cancelling_a_pending_request_never_creates_a_paid_entry(): void
    {
        $request = $this->storeRequest(10000.00, 4000.00);

        $this->actingAs($this->admin)
            ->patch(route('expense_request.status', $request), ['status' => 'cancelled'])
            ->assertRedirect();

        $this->assertDatabaseCount('agent_deductions', 0);
    }
}
