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

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
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

    /**
     * An approved expense reaches the Agent Ledger, and reaches it as the NET
     * amount — not the gross, and not twice.
     *
     * Renamed from approved_item_appears_in_deductions_tab_and_not_in_commission.
     * That name described UI that no longer exists. The Deductions tab is
     * deductions-only: AgentReportController::index() builds it with
     * where('account', AgentDeduction::ACCOUNT_DEDUCTION), and the comment there
     * records Cyd's 2026-10-23 decision to keep approval-generated 'Paid' rows
     * off it. The Commission tab was removed outright in 3e83726c. So the old
     * test asserted `Paid` on a tab that can never show it, and searched for
     * '10,000.00' on the Payments tab — which renders that number as the item
     * AMOUNT column, one column reorder away from flipping for no good reason.
     *
     * What survives is the claim worth keeping, and the one nothing else makes:
     * the DB-level tests above prove the Paid row is WRITTEN, none prove it
     * reaches a SCREEN. That write-vs-display gap is how the CSV Status column
     * sat at 'N/A' for months.
     *
     * The ledger shows 6,000 in Payments (the net Paid entry) plus 4,000 in the
     * same column from the payment recorded on the transaction, totalling the
     * full 10,000 gross. That is deliberate — AgentReportController::
     * agentLedgerData() carries the "No double count: an approval row already
     * nets out the payment" note — and it is pinned from the other end by
     * AgentLedgerPaymentReflectsExpensePaymentsTest::
     * approval_paid_row_and_expense_payment_do_not_double_count. Read both
     * before changing either.
     *
     * (Deliberately citing method names, not line numbers: the numbers in this
     * docblock's first draft were stale within the hour, which is the same decay
     * that killed the old test name.)
     */
    #[Test]
    public function approved_item_reaches_the_ledger_as_net_once(): void
    {
        $request = $this->storeRequest(10000.00, 4000.00);
        $this->approve($request);

        $response = $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'report', 'agent_id' => $this->agent->id]))
            ->assertOk();

        // The ledger's Payments column is a TOTAL, not a per-row breakdown: it
        // sums the net Paid entry (6,000) and the payment already recorded on
        // the transaction (4,000) to the full gross. Assert the total exactly.
        $row = $this->ledgerRow($response->getContent(), $this->agent->first_name . ' ' . $this->agent->last_name);
        $this->assertSame('₱10,000.00', $row[5], 'net Paid entry + transaction payment = the gross');

        // The net-vs-gross guard. If the Paid entry were ever stored gross, the
        // column would read 14,000 (10,000 + 4,000), not 10,000.
        $this->assertNotSame('₱14,000.00', $row[5], 'the Paid entry must be stored net, not gross');

        // Balance = -payments = -10,000 (nothing else on this ledger).
        $this->assertSame('₱-10,000.00', $row[6]);
    }

    #[Test]
    public function pending_item_is_not_yet_a_paid_deduction(): void
    {
        // Replaces pending_item_still_appears_in_commission_tab(). The tab that
        // used to show it is gone; the fact it encoded — a pending item is not
        // yet Paid — is still worth pinning.
        $this->storeRequest(10000.00, 0.00);

        $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'deductions']))
            ->assertOk()
            ->assertDontSee('Placement fee');
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
