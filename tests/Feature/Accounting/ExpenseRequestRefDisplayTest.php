<?php

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regression test for: "there are two Ref# 2001 on /expense-request —
 * we create ref id wrong."
 *
 * Root cause: reference_no generation is unique in the DB, but the index
 * view renders one row per line item and repeats the request-level columns
 * (Ref#, Date, User, Status, Branch) on every item row. A request with 2
 * items therefore shows the same Ref# twice.
 *
 * Contract: each request-level value must appear exactly once per request,
 * no matter how many line items the request has.
 *
 * HOW THESE ARE ASSERTED (card 6afb5d69a56c2a5272d42daf): the request-level
 * cells carry rowspan and put the value on its own line —
 *
 *     <td class="font-mono" rowspan="2">
 *                                                   2001
 *     </td>
 *
 * — so the old `>2001<` string could never match: it assumed the value sat
 * flush against both tags. These tests now count occurrences of the value
 * INSIDE the request-level cell, which is the behaviour the card is about
 * ("the ref appears once, not once per line item") rather than the shape of
 * the HTML around it. Counting inside the cell is stricter than a bare
 * substring count: three copies inside one cell still fail.
 */
class ExpenseRequestRefDisplayTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Branch $branch;
    private User $billing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $this->billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);
    }

    /**
     * A request with a branch and a status, exactly as the module creates
     * one. Both were previously omitted: with no branch_id the row is
     * invisible to any branch-locked viewer (index() adds
     * ->where('branch_id', …) under isBranchLocked()), so the fixture only
     * rendered because this user happens to be billing without a branch —
     * the branch scoping the class is meant to exercise was never applied.
     *
     * @param  list<array<string, mixed>>  $items
     */
    private function makeRequestWithItems(string $ref, array $items, ?string $date = null): ExpenseRequest
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);

        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->billing->id,
            'branch_id'    => $this->branch->id,
            'reference_no' => $ref,
            'date'         => $date ?? now()->toDateString(),
            'status'       => ExpenseRequest::STATUS_PENDING,
        ]);

        foreach ($items as $item) {
            $request->items()->create(array_merge([
                'account_id' => $account->id,
                'particular' => 'Test item',
            ], $item));
        }

        return $request;
    }

    /**
     * How many times $value is rendered inside the request-level Ref# cell.
     *
     * The cell is the one carrying `font-mono` AND `rowspan` (the item rows
     * have neither), so this cannot drift onto a per-item cell.
     */
    private function refCellOccurrences(string $html, string $value): int
    {
        preg_match_all(
            '#<td[^>]*\bfont-mono\b[^>]*\browspan="[^"]*"[^>]*>(.*?)</td>#s',
            $html,
            $cells
        );

        $total = 0;
        foreach ($cells[1] as $cell) {
            $total += substr_count($cell, $value);
        }

        return $total;
    }

    #[Test]
    public function reference_no_appears_once_per_request_even_with_multiple_items(): void
    {
        $this->makeRequestWithItems('2001', [
            ['charge' => 'office', 'currency' => 'PHP', 'amount' => 1000.00],
            ['charge' => 'agent', 'currency' => 'PHP', 'amount' => 500.00],
        ]);

        $response = $this->actingAs($this->billing)->get(route('expense_request.index'));

        $response->assertOk();
        $this->assertSame(1, $this->refCellOccurrences($response->getContent(), '2001'));
    }

    #[Test]
    public function multiple_requests_each_show_their_ref_exactly_once(): void
    {
        $this->makeRequestWithItems('2001', [
            ['charge' => 'office', 'currency' => 'PHP', 'amount' => 1000.00],
            ['charge' => 'agent', 'currency' => 'PHP', 'amount' => 500.00],
        ]);
        $this->makeRequestWithItems('2002', [
            ['charge' => 'office', 'currency' => 'PHP', 'amount' => 250.00],
        ]);

        $content = $this->actingAs($this->billing)->get(route('expense_request.index'))->getContent();

        $this->assertSame(1, $this->refCellOccurrences($content, '2001'));
        $this->assertSame(1, $this->refCellOccurrences($content, '2002'));
    }

    #[Test]
    public function request_level_date_is_not_repeated_per_line_item(): void
    {
        $request = $this->makeRequestWithItems('2001', [
            ['charge' => 'office', 'currency' => 'PHP', 'amount' => 1000.00],
            ['charge' => 'agent', 'currency' => 'PHP', 'amount' => 500.00],
        ]);

        // The index renders the request-level Date column as
        // $request->date->format('Y-m-d') — not created_at, and not with a
        // time. It must appear exactly once for the two line items.
        $expected = $request->date->format('Y-m-d');

        $content = $this->actingAs($this->billing)->get(route('expense_request.index'))->getContent();

        // Count the date inside the request-level (rowspan) cells: once per
        // request, regardless of how many line items it carries.
        preg_match_all('#<td[^>]*\browspan="[^"]*"[^>]*>(.*?)</td>#s', $content, $cells);

        $occurrences = 0;
        foreach ($cells[1] as $cell) {
            if (trim($cell) === $expected) {
                $occurrences++;
            }
        }

        $this->assertSame(1, $occurrences, 'The request-level date must appear once per request, not once per line item.');
    }

    #[Test]
    public function single_item_request_still_shows_ref_once(): void
    {
        $this->makeRequestWithItems('2003', [
            ['charge' => 'office', 'currency' => 'PHP', 'amount' => 100.00],
        ]);

        $response = $this->actingAs($this->billing)->get(route('expense_request.index'));

        $response->assertOk();
        $this->assertSame(1, $this->refCellOccurrences($response->getContent(), '2003'));
    }
}
