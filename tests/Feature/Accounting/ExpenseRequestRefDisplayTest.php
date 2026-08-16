<?php

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\Agency;
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
 */
class ExpenseRequestRefDisplayTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $billing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);
    }

    private function makeRequestWithItems(string $ref, array $items): ExpenseRequest
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);

        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->billing->id,
            'reference_no' => $ref,
            'date'         => now()->toDateString(),
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

    #[Test]
    public function reference_no_appears_once_per_request_even_with_multiple_items(): void
    {
        $this->makeRequestWithItems('2001', [
            ['charge' => 'office', 'currency' => 'PHP', 'amount' => 1000.00],
            ['charge' => 'agent', 'currency' => 'PHP', 'amount' => 500.00],
        ]);

        $response = $this->actingAs($this->billing)->get(route('expense_request.index'));

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), '>2001<'));
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

        $this->assertSame(1, substr_count($content, '>2001<'));
        $this->assertSame(1, substr_count($content, '>2002<'));
    }

    #[Test]
    public function request_level_date_is_not_repeated_per_line_item(): void
    {
        $date = '2026-08-12';
        $this->makeRequestWithItems('2001', [
            ['charge' => 'office', 'currency' => 'PHP', 'amount' => 1000.00],
            ['charge' => 'agent', 'currency' => 'PHP', 'amount' => 500.00],
        ]);

        $content = $this->actingAs($this->billing)->get(route('expense_request.index'))->getContent();

        $this->assertSame(1, substr_count($content, '>'.$date.'<'));
    }

    #[Test]
    public function single_item_request_still_shows_ref_once(): void
    {
        $this->makeRequestWithItems('2003', [
            ['charge' => 'office', 'currency' => 'PHP', 'amount' => 100.00],
        ]);

        $response = $this->actingAs($this->billing)->get(route('expense_request.index'));

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), '>2003<'));
    }
}
