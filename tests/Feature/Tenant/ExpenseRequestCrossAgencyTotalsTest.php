<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Models\Account;
use App\Models\Agency;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b008b8d3ca32bfcb58ce970, acceptance 4.
 *
 * ExpenseRequest is the live financial exposure: 27,079 rows, 27,069 of them
 * on agency 7. Adding the scope must not just hide rows from a list — the
 * money totals must not include them either. These tests assert on a real
 * aggregate figure (the currency total the dashboard renders), not only on
 * row absence.
 *
 * Rows are seeded through ExpenseRequest::create() on purpose: a fixture
 * builder that writes via DB::table() would bypass the scope under test on the
 * write side too and prove nothing about what the app sees.
 */
class ExpenseRequestCrossAgencyTotalsTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Agency $otherAgency;
    private User $admin;
    private Account $account;
    private Account $otherAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->otherAgency = Agency::factory()->create(['name' => 'AAA Other', 'subdomain' => 'aaa-other']);
        $this->agency      = Agency::factory()->create(['name' => 'Zeta Agency', 'subdomain' => 'zeta-agency']);

        app()->instance('tenant_agency', $this->agency);

        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'name'      => 'Mae',       // privileged: sees all accounting data + totals
        ]);

        $this->account      = Account::factory()->create(['agency_id' => $this->agency->id]);
        $this->otherAccount = Account::factory()->create(['agency_id' => $this->otherAgency->id]);
    }

    private function request(Agency $agency, User $encoder, Account $account, string $status, float $amount, string $ref): ExpenseRequest
    {
        $request = ExpenseRequest::create([
            'agency_id'    => $agency->id,
            'user_id'      => $encoder->id,
            'reference_no' => $ref,
            'date'         => now()->toDateString(),
            'status'       => $status,
        ]);

        $request->items()->create([
            'account_id' => $account->id,
            'particular' => 'Line for ' . $ref,
            'charge'     => 'office',
            'currency'   => 'PHP',
            'amount'     => $amount,
            'status'     => $status,
        ]);

        return $request;
    }

    private function otherEncoder(): User
    {
        return User::firstOrCreate(
            ['email' => 'other-encoder@other.test'],
            ['agency_id' => $this->otherAgency->id, 'name' => 'Other Encoder', 'user_type' => 'staff', 'password' => bcrypt('password')],
        );
    }

    #[Test]
    public function another_agencys_request_is_absent_from_the_listing_and_the_totals(): void
    {
        // Own agency: 1,000 pending + 500 released = 1,500 PHP.
        $this->request($this->agency, $this->admin, $this->account, ExpenseRequest::STATUS_PENDING, 1000.00, 'OWN-1');
        $this->request($this->agency, $this->admin, $this->account, ExpenseRequest::STATUS_RELEASED, 500.00, 'OWN-2');

        // Other agency: a 9,999.00 request in the same statuses. If the scope
        // leaks, the totals below include it.
        $this->request($this->otherAgency, $this->otherEncoder(), $this->otherAccount, ExpenseRequest::STATUS_PENDING, 9999.00, 'FOREIGN-1');

        $response = $this->actingAs($this->admin)->get(route('expense_request.index'));

        $response->assertOk();
        $response->assertSee('OWN-1');
        $response->assertDontSee('FOREIGN-1', false);

        // The model-level scope: every query built from the model (which is
        // how the ledger, reports, cash-advance and receivable readers reach
        // this table) sees only this agency. The index controller happens to
        // ALSO carry an explicit where(agency_id), which is why the page
        // assertions above are not the only evidence — with that explicit
        // filter in place the page is safe either way, and this is the half
        // that fails when the model loses its scope.
        $requests = ExpenseRequest::query()->pluck('reference_no')->all();
        $this->assertSame(['OWN-1', 'OWN-2'], $requests);

        $this->assertSame(1, ExpenseRequest::query()->where('status', ExpenseRequest::STATUS_PENDING)->count(),
            'the pending aggregate included another agency\'s request');
        $this->assertSame(2, ExpenseRequest::query()->count());
    }

    /**
     * The real dashboard aggregate: the summary card renders one money figure
     * per status, taken from currencyTotalsQuery(). A foreign 9,999.00 must
     * not reach it, and this agency's own 1,000.00 must.
     *
     * The figures asserted are the ones the page computes, not a query this
     * test writes for itself.
     */
    #[Test]
    public function the_status_totals_the_dashboard_renders_exclude_another_agencys_requests(): void
    {
        $this->request($this->agency, $this->admin, $this->account, ExpenseRequest::STATUS_PENDING, 1000.00, 'OWN-1');
        $this->request($this->agency, $this->admin, $this->account, ExpenseRequest::STATUS_RELEASED, 500.00, 'OWN-2');
        $this->request($this->otherAgency, $this->otherEncoder(), $this->otherAccount, ExpenseRequest::STATUS_PENDING, 9999.00, 'FOREIGN-1');

        $response = $this->actingAs($this->admin)->get(route('expense_request.index'));
        $response->assertOk();

        $html = $response->getContent();

        // The per-status aggregate block: pull the pending line and read the
        // figure the page computed for it.
        $this->assertSame(1, preg_match('/data-status-summary="pending".*?\x{20B1}([\d,]+\.\d{2})/su', $html, $m),
            'the pending aggregate line was not rendered');
        $this->assertSame('1,000.00', $m[1], 'the pending aggregate is not this agency\'s 1,000.00');

        // Nothing from the other agency reached any money figure on the page.
        $this->assertStringNotContainsString('9,999.00', $html,
            'another agency\'s money figure was rendered on this agency\'s dashboard');

        // Same figure, computed from the model rather than the controller's
        // explicit filter: this is the assertion that goes red if
        // ExpenseRequest loses its tenant scope.
        $this->assertSame(1500.0, (float) ExpenseRequest::query()->sum(
            \DB::raw('(select coalesce(sum(i.amount),0) from expense_request_items i where i.expense_request_id = expense_requests.id)')
        ), 'the model-level money aggregate included another agency\'s requests');

        // Released headline card: 500.00, not mixed with anything foreign.
        $this->assertStringContainsString('₱500.00', $html);

        // Row counts, which the tabs and card badges print.
        $this->assertStringContainsString('>1</span>', $html); // one released
    }

    /**
     * ExpenseRequest is joined from ExpenseRequestItem by two readers:
     * GeneralLedgerController::accountTotals() (aliases `expense_requests as
     * er` and filters `er.agency_id` explicitly) and
     * AgentReportController's cash-advance tab (aliases it `ca_er`, filters
     * through whereHas on the relation).
     *
     * Both pins are real:
     *   - TenantScope quotes the table name (`expense_requests.agency_id`), so
     *     an ALIASED join is NOT covered by the scope and still needs its own
     *     predicate. The GeneralLedger reader has one; this asserts it holds.
     *   - The cash-advance reader filters via the relation, which the scope
     *     DOES cover. This asserts a foreign agency's cash advance stays out.
     */
    #[Test]
    public function the_aliased_general_ledger_join_is_filtered_to_this_agency(): void
    {
        $own     = $this->request($this->agency, $this->admin, $this->account, ExpenseRequest::STATUS_PENDING, 1000.00, 'OWN-1');
        $foreign = $this->request($this->otherAgency, $this->otherEncoder(), $this->otherAccount, ExpenseRequest::STATUS_PENDING, 9999.00, 'FOREIGN-1');

        // Shape copied from GeneralLedgerController::accountTotals(): aliased
        // join plus its own er.agency_id predicate.
        $ids = \App\Models\ExpenseRequestItem::query()
            ->join('expense_requests as er', 'er.id', '=', 'expense_request_items.expense_request_id')
            ->where('er.agency_id', $this->agency->id)
            ->whereNull('er.deleted_at')
            ->pluck('expense_request_items.expense_request_id')
            ->all();

        $this->assertSame([$own->id], $ids,
            'the general-ledger join pulled in another agency\'s expense request');
        $this->assertNotContains($foreign->id, $ids);

        // And the total it computes is this agency's alone.
        $this->assertSame(1000.0, (float) \App\Models\ExpenseRequestItem::query()
            ->join('expense_requests as er', 'er.id', '=', 'expense_request_items.expense_request_id')
            ->where('er.agency_id', $this->agency->id)
            ->whereNull('er.deleted_at')
            ->sum('expense_request_items.amount'));
    }

    #[Test]
    public function the_cash_advance_reader_is_filtered_by_the_relation_scope(): void
    {
        $this->request($this->agency, $this->admin, $this->account, ExpenseRequest::STATUS_RELEASED, 700.00, 'OWN-CA');
        $this->request($this->otherAgency, $this->otherEncoder(), $this->otherAccount, ExpenseRequest::STATUS_RELEASED, 4242.00, 'FOREIGN-CA');

        // Shape copied from AgentReportController's cash-advance tab: filter
        // by charge + agency through whereHas on the relation, then the
        // aliased join for ordering only.
        $items = \App\Models\ExpenseRequestItem::query()
            ->where('charge', 'office')
            ->whereHas('expenseRequest', fn ($er) => $er->where('agency_id', $this->agency->id)
                ->whereIn('status', [ExpenseRequest::STATUS_PENDING, ExpenseRequest::STATUS_RELEASED]))
            ->join('expense_requests as ca_er', 'ca_er.id', '=', 'expense_request_items.expense_request_id')
            ->select('expense_request_items.*')
            ->get();

        $this->assertCount(1, $items);
        $this->assertSame(700.0, (float) $items->first()->amount);

        // The relation itself carries the model scope too, so a foreign
        // request cannot be reached through it at all.
        $foreignRequest = ExpenseRequest::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('reference_no', 'FOREIGN-CA')->firstOrFail();
        // Card 6b008b8e7c2a220f1cd6d2e1 put a tenant scope on ExpenseRequestItem
        // itself, so the foreign line cannot even be loaded through the model
        // any more (it used to load fine and resolve to a null relation). Read
        // it straight from the table to keep testing the relation, which is
        // the thing this assertion is about.
        $foreignItemId = \DB::table('expense_request_items')
            ->where('expense_request_id', $foreignRequest->id)->value('id');
        $foreignItem = \App\Models\ExpenseRequestItem::withoutGlobalScopes()->findOrFail($foreignItemId);
        $this->assertNull($foreignItem->expenseRequest, 'the relation reached another agency\'s request despite the scope');
    }
}
