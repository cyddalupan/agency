<?php

namespace Tests\Feature\Tenant;

use App\Exceptions\UnboundTenantException;
use App\Models\Account;
use App\Models\Agency;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\Scopes\ExpenseRequestItemTenantScope;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b008b8e7c2a220f1cd6d2e1 — `expense_request_items` has no agency_id.
 * Its tenant is inherited from the parent request, and as of this card that
 * inheritance is ENFORCED by a global scope, not just written in a comment.
 *
 * Every test seeds items under two agencies' requests, binds one agency, and
 * asserts the query sees its own items AND NOT the other agency's. The
 * negative half is the point: "A's items are there" also passes when the
 * scope is missing.
 *
 * Items are seeded through the relation on purpose; a DB::table() fixture
 * would bypass the code under test on the write side too.
 */
class ExpenseRequestItemTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Agency $otherAgency;
    private User $admin;
    private User $otherAdmin;
    private Account $mainOffice;
    private Account $salary;
    private Account $pltd;

    protected function setUp(): void
    {
        parent::setUp();

        $this->otherAgency = Agency::factory()->create(['name' => 'AAA Other', 'subdomain' => 'aaa-other']);
        $this->agency      = Agency::factory()->create(['name' => 'Zeta Agency', 'subdomain' => 'zeta-agency']);

        app()->instance('tenant_agency', $this->agency);

        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'name'      => 'Mae',   // privileged: sees all accounting data + totals
        ]);
        $this->otherAdmin = User::factory()->create([
            'agency_id' => $this->otherAgency->id,
            'user_type' => 'admin',
            'name'      => 'Mae',
        ]);

        $this->mainOffice = Account::create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'OFFICE',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
        $this->salary = Account::create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $this->mainOffice->id,
            'name'        => 'Salary',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
        $this->pltd = Account::create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $this->mainOffice->id,
            'name'        => 'PLDT',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
    }

    /** An account in another agency, so its items group under a separate id. */
    private function foreignAccount(): Account
    {
        $main = Account::create([
            'agency_id'   => $this->otherAgency->id,
            'parent_id'   => null,
            'name'        => 'OFFICE',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);

        return Account::create([
            'agency_id'   => $this->otherAgency->id,
            'parent_id'   => $main->id,
            'name'        => 'Salary',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
    }

    private function request(Agency $agency, User $encoder, string $ref, string $status = ExpenseRequest::STATUS_RELEASED): ExpenseRequest
    {
        return ExpenseRequest::create([
            'agency_id'    => $agency->id,
            'user_id'      => $encoder->id,
            'reference_no' => $ref,
            'date'         => now()->subDays(2)->toDateString(),
            'status'       => $status,
        ]);
    }

    private function line(ExpenseRequest $request, Account $account, float $amount, string $status = ExpenseRequest::STATUS_RELEASED, string $particular = 'Line'): ExpenseRequestItem
    {
        return $request->items()->create([
            'account_id' => $account->id,
            'charge'     => 'office',
            'currency'   => 'PHP',
            'amount'     => $amount,
            'status'     => $status,
            'particular' => $particular,
        ]);
    }

    /**
     * Acceptance 1: bind agency A, seed items under A and under B, and the
     * model query returns A's items and not B's.
     *
     * Mutation check done by hand on 2026-11-22: removing
     * `static::addGlobalScope(...)` from ExpenseRequestItem::booted() turns
     * this test red (4 items instead of 2, and B's ids present).
     */
    #[Test]
    public function item_queries_return_only_the_bound_agencys_items(): void
    {
        $own     = $this->request($this->agency, $this->admin, 'OWN-1');
        $foreign = $this->request($this->otherAgency, $this->otherAdmin, 'FOREIGN-1');

        $ownSalary   = $this->line($own, $this->salary, 1000.00);
        $ownPltd     = $this->line($own, $this->pltd, 250.50);
        $foreignLine = $this->line($foreign, $this->foreignAccount(), 9999.00);

        $ids = ExpenseRequestItem::query()->orderBy('id')->pluck('id')->all();

        $this->assertSame([$ownSalary->id, $ownPltd->id], $ids,
            'the item query did not return exactly this agency\'s items');
        $this->assertNotContains($foreignLine->id, $ids);
        $this->assertSame(2, ExpenseRequestItem::query()->count());

        // The scope filters, it does not hide: the foreign line is still in
        // the table, reachable only by a deliberate cross-tenant read.
        $this->assertSame($this->otherAgency->id, (int) ExpenseRequestItem::withoutGlobalScopes()
            ->findOrFail($foreignLine->id)->expenseRequest()->withoutGlobalScopes()->firstOrFail()->agency_id);

        // Nested through the request: a parent's items relation carries the
        // scope too, and the foreign parent's relation is empty when the
        // foreign request is read without its own scope.
        $foreignParent = ExpenseRequest::withoutGlobalScope(TenantScope::class)->findOrFail($foreign->id);
        $this->assertCount(0, $foreignParent->items, 'the items relation served another agency\'s lines');
    }

    /**
     * Acceptance 2. The GeneralLedger account totals are money. A scope that
     * silently zeroes the aggregate is the failure mode this pins down.
     *
     * Two halves:
     *  (a) the SQL the controller now runs (join + constrain) must equal the
     *      pre-card SQL (join + its own er.agency_id predicate) to the cent.
     *      Tenant is unbound here, so the model scope is a no-op and the
     *      "before" shape is exactly what shipped.
     *  (b) the rendered page, computed through accountTotals() with agency A
     *      bound, must show A's real numbers.
     */
    #[Test]
    public function general_ledger_totals_are_unchanged_by_the_scope(): void
    {
        $own = $this->request($this->agency, $this->admin, 'OWN-1');
        $this->line($own, $this->salary, 1500.50);
        $this->line($own, $this->salary, 2500.50);
        $this->line($own, $this->pltd, 900.00);
        // Cancelled never reaches the ledger.
        $this->line($own, $this->pltd, 5000.00, ExpenseRequest::STATUS_CANCELLED);

        // Another agency, same account names but its own accounts: must not
        // inflate A's ledger.
        $foreign = $this->request($this->otherAgency, $this->otherAdmin, 'FOREIGN-1', ExpenseRequest::STATUS_PENDING);
        $this->line($foreign, $this->foreignAccount(), 9999.00, ExpenseRequest::STATUS_PENDING);

        /** Pre-card shape: aliased join plus the controller's own predicate. */
        $before = function (int $agencyId): array {
            return ExpenseRequestItem::withoutGlobalScopes()
                ->join('expense_requests as er', 'er.id', '=', 'expense_request_items.expense_request_id')
                ->where('er.agency_id', $agencyId)
                ->whereNull('er.deleted_at')
                ->whereNotIn('expense_request_items.status', ['cancelled'])
                ->groupBy('expense_request_items.account_id', 'expense_request_items.currency')
                ->selectRaw('expense_request_items.account_id as account_id')
                ->selectRaw('expense_request_items.currency as currency')
                ->selectRaw('COUNT(*) as tx_count')
                ->selectRaw('SUM(expense_request_items.amount) as total')
                ->get()
                ->map(fn ($r) => [(int) $r->account_id => [(int) $r->tx_count, round((float) $r->total, 2)]])
                ->collapse()
                ->map(fn ($pair, $accountId) => ['account_id' => (int) $accountId, 'count' => $pair[0], 'total' => $pair[1]])
                ->sortBy('account_id')
                ->values()
                ->all();
        };

        /** The statement GeneralLedgerController::accountTotals() now runs. */
        $after = function (int $agencyId): array {
            return ExpenseRequestItemTenantScope::constrain(
                ExpenseRequestItem::query()
                    ->join('expense_requests as er', 'er.id', '=', 'expense_request_items.expense_request_id')
                    ->where('er.agency_id', $agencyId)
                    ->whereNull('er.deleted_at')
                    ->whereNotIn('expense_request_items.status', ['cancelled'])
                    ->groupBy('expense_request_items.account_id', 'expense_request_items.currency')
                    ->selectRaw('expense_request_items.account_id as account_id')
                    ->selectRaw('expense_request_items.currency as currency')
                    ->selectRaw('COUNT(*) as tx_count')
                    ->selectRaw('SUM(expense_request_items.amount) as total')
                    ->toBase(),
                'expense_request_items',
                $agencyId,
            )->get()
                ->map(fn ($r) => [(int) $r->account_id => [(int) $r->tx_count, round((float) $r->total, 2)]])
                ->collapse()
                ->map(fn ($pair, $accountId) => ['account_id' => (int) $accountId, 'count' => $pair[0], 'total' => $pair[1]])
                ->sortBy('account_id')
                ->values()
                ->all();
        };

        $b = $before($this->agency->id);
        $a = $after($this->agency->id);

        $this->assertSame($b, $a, 'the constrained general-ledger aggregate differs from the pre-card one');
        $this->assertSame(4901.0, (float) array_sum(array_column($a, 'total')),
            'the general-ledger total is not this agency\'s non-cancelled 4,901.00');
        // 3 lines count: two Salary + one PLDT; the cancelled 5,000.00 is out.
        $this->assertSame(3, (int) array_sum(array_column($a, 'count')));

        // Issuing the very same statement a second time must not double-count
        // (a whereIn against a subquery is not a join).
        $this->assertSame($a, $after($this->agency->id));

        // (b) the page. Bound to agency A, the ledger renders A's figures and
        // none of B's.
        app()->instance('tenant_agency', $this->agency);

        $response = $this->actingAs($this->admin)->get(route('general_ledger.index'));
        $response->assertOk();

        $html = $response->getContent();
        $this->assertStringContainsString('4,901.00', $html, 'the general-ledger grand total is not 4,901.00');
        $this->assertStringContainsString('4,001.00', $html, 'the Salary subtotal is not 4,001.00');
        $this->assertStringContainsString('900.00', $html, 'the PLDT subtotal is not 900.00');
        $this->assertStringNotContainsString('9,999', $html, 'another agency\'s amount reached the ledger');
    }

    /**
     * Acceptance 3. The raw query-builder readers are aimed at
     * expense_requests and never see the item model, so the scope cannot
     * reach them. This asserts the one that could under-report money — the
     * GeneralLedger join — is constrained even when nothing else in the
     * statement mentions the bound agency, and that the filter bites through
     * the alias (`er`) rather than being ignored.
     */
    #[Test]
    public function the_raw_general_ledger_join_is_constrained_through_the_alias(): void
    {
        $own     = $this->request($this->agency, $this->admin, 'OWN-1');
        $foreign = $this->request($this->otherAgency, $this->otherAdmin, 'FOREIGN-1');
        $this->line($own, $this->salary, 1000.00);
        $this->line($foreign, $this->foreignAccount(), 4242.00);

        // No er.agency_id predicate here on purpose: the constraint under test
        // is the one ExpenseRequestItemTenantScope adds.
        $sum = ExpenseRequestItemTenantScope::constrain(
            ExpenseRequestItem::query()
                ->join('expense_requests as er', 'er.id', '=', 'expense_request_items.expense_request_id')
                ->toBase(),
            'expense_request_items',
            (int) $this->agency->id,
        )->selectRaw('SUM(expense_request_items.amount) as total')->value('total');

        $this->assertSame(1000.0, (float) $sum,
            'the constrained join included another agency\'s lines');
    }

    /**
     * Acceptance 3, the other raw readers. Each one is anchored on
     * expense_requests (the request list pages on it, so the ALIASED
     * `expense_requests as dup_items/agg_items` join cannot be used to reach a
     * foreign line) or filtered through the scoped relation. This asserts
     * that shape still yields foreign-free figures while the model scope is
     * active.
     */
    #[Test]
    public function the_list_aggregate_and_duplicate_scan_stay_within_the_agency(): void
    {
        $own = $this->request($this->agency, $this->admin, 'OWN-1', ExpenseRequest::STATUS_PENDING);
        $this->line($own, $this->salary, 1000.00, ExpenseRequest::STATUS_PENDING);
        $this->line($own, $this->salary, 1000.00, ExpenseRequest::STATUS_PENDING);   // duplicate pair

        $foreign = $this->request($this->otherAgency, $this->otherAdmin, 'FOREIGN-1', ExpenseRequest::STATUS_PENDING);
        $this->line($foreign, $this->foreignAccount(), 9999.00, ExpenseRequest::STATUS_PENDING);

        // Shape of currencyTotalsQuery(): page on ExpenseRequest (scoped).
        $totals = ExpenseRequest::query()
            ->join('expense_request_items as agg_items', 'agg_items.expense_request_id', '=', 'expense_requests.id')
            ->toBase()
            ->selectRaw('agg_items.currency as cur, SUM(agg_items.amount) as total')
            ->groupBy('agg_items.currency')
            ->pluck('total', 'cur')
            ->all();

        $this->assertSame(['PHP' => 2000.0], array_map('floatval', $totals),
            'the list totals included another agency\'s lines');

        // Shape of the duplicate-fingerprint scan: same anchor.
        $rows = ExpenseRequest::query()
            ->join('expense_request_items as dup_items', 'dup_items.expense_request_id', '=', 'expense_requests.id')
            ->toBase()
            ->select(['dup_items.id', 'dup_items.amount'])
            ->get();

        $this->assertCount(2, $rows, 'the duplicate scan read another agency\'s lines');
    }

    /**
     * Acceptance 4. A web request with no agency throws instead of returning
     * every agency's items. Same exception, same resolution as the shipped
     * scope — the item scope does not invent its own binding rules.
     */
    #[Test]
    public function an_unbound_web_request_throws_for_items(): void
    {
        $this->request($this->agency, $this->admin, 'OWN-1');
        $foreignRequest = $this->request($this->otherAgency, $this->otherAdmin, 'FOREIGN-1');
        $this->line($foreignRequest, $this->foreignAccount(), 100.00);

        // Drop the container binding and the session: nothing to resolve.
        app()->forgetInstance('tenant_agency');
        session()->forget('tenant_agency_id');
        // Judge this as a web request (PHPUnit boots through the console
        // kernel, where the shipped scope deliberately fails open).
        TenantScope::testingAsWebRequest(true);

        try {
            $this->expectException(UnboundTenantException::class);
            ExpenseRequestItem::query()->count();
        } finally {
            TenantScope::testingAsWebRequest(false);
        }
    }

    /**
     * The four branch-divergent rows (item branch_id != parent branch_id) are
     * legitimate main-office postings, and the tenant scope must not touch
     * branch filtering at all: the line still shows for the tenant, with its
     * own branch_id intact.
     */
    #[Test]
    public function a_cross_branch_line_stays_visible_to_its_tenant(): void
    {
        $mainBranch = \App\Models\Branch::create(['agency_id' => $this->agency->id, 'name' => 'Main Office']);
        $subBranch  = \App\Models\Branch::create(['agency_id' => $this->agency->id, 'name' => 'Cebu']);

        $request = $this->request($this->agency, $this->admin, 'OWN-XB');
        $request->update(['branch_id' => $mainBranch->id]);

        $line = $this->line($request, $this->salary, 750.00, ExpenseRequest::STATUS_PENDING);
        $line->update(['branch_id' => $subBranch->id]);

        $found = ExpenseRequestItem::query()->findOrFail($line->id);

        $this->assertSame((int) $subBranch->id, (int) $found->branch_id, 'the per-line branch was overwritten');
        $this->assertSame((int) $mainBranch->id, (int) $found->expenseRequest->branch_id);
        $this->assertSame($this->agency->id, (int) $found->expenseRequest->agency_id);
    }
}
