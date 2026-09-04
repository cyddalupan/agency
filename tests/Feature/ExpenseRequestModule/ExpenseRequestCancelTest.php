<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agent;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits 2026-08-18 — Cancel status: an admin can reject a transaction they
 * do not want to accept. Cancelled requests must be excluded from the index
 * totals (pending/received) and from duplicate detection, while still being
 * listed with their own badge and logged in the status history.
 */
class ExpenseRequestCancelTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Branch $branch;
    private Country $country;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $this->country = Country::factory()->create();
    }

    private function createRequest(float $amount = 1000.00): ExpenseRequest
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id, 'charge_type' => 'agent']);
        $sub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $account->id,
            'charge_type' => 'agent',
        ]);

        $agent = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $this->branch->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);

        $this->actingAs($this->user)
            ->post(route('expense_request.store'), [
                'branch_id' => $this->branch->id,
                'lines'     => [
                    [
                        'charge'          => 'agent',
                        'sub_account_id'  => $sub->id,
                        'agent_id'        => $agent->id,
                        'applicant_id'    => $applicant->id,
                        'country_id'      => $this->country->id,
                        'currency'        => 'PHP',
                        'amount'          => $amount,
                        'main_account_id' => $account->id,
                        'particular'      => 'Advance',
                    ],
                ],
            ]);

        return ExpenseRequest::firstOrFail();
    }

    #[Test]
    public function admin_can_cancel_an_expense_request_and_history_is_logged(): void
    {
        $request = $this->createRequest();

        $this->actingAs($this->user)
            ->patch(route('expense_request.status', $request), ['status' => 'cancelled', 'note' => 'Duplicate entry'])
            ->assertRedirect();

        $this->assertSame('cancelled', $request->fresh()->status);

        $this->assertDatabaseHas('expense_request_histories', [
            'expense_request_id' => $request->id,
            'from_status'        => 'pending',
            'to_status'          => 'cancelled',
            'note'               => 'Duplicate entry',
        ]);
    }

    #[Test]
    public function only_admin_can_cancel_an_expense_request(): void
    {
        $request = $this->createRequest();
        $staff = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'staff']);

        $this->actingAs($staff)
            ->patch(route('expense_request.status', $request), ['status' => 'cancelled'])
            ->assertForbidden();

        $this->assertSame('pending', $request->fresh()->status);
    }

    #[Test]
    public function show_page_lists_cancel_as_a_status_option(): void
    {
        $request = $this->createRequest();

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.show', $request))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('value="cancelled"', $html);
    }

    #[Test]
    public function cancelled_requests_are_excluded_from_index_totals(): void
    {
        $this->createRequest(1000.00); // stays pending
        $cancelled = $this->createRequest(500.00);

        $this->actingAs($this->user)
            ->patch(route('expense_request.status', $cancelled), ['status' => 'cancelled']);

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->getContent();

        // Only the pending 1,000 counts toward the totals; the cancelled 500 is excluded.
        $this->assertStringContainsString('₱1,000.00', $html);
        $this->assertStringNotContainsString('₱1,500.00', $html);
    }

    #[Test]
    public function cancelled_requests_show_their_own_badge_on_index(): void
    {
        $request = $this->createRequest();
        $this->actingAs($this->user)
            ->patch(route('expense_request.status', $request), ['status' => 'cancelled']);

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('badge-error', $html);
        $this->assertStringContainsString('Cancelled', $html);
    }

    #[Test]
    public function cancelled_requests_do_not_flag_duplicates(): void
    {
        // Two identical transactions (same amount + same applicant) -> duplicate pair.
        $this->createRequest(1000.00);
        $twin = $this->createRequest(1000.00);

        $this->actingAs($this->user)
            ->patch(route('expense_request.status', $twin), ['status' => 'cancelled']);

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->getContent();

        // The surviving transaction is no longer flagged as a duplicate.
        $this->assertStringNotContainsString('Duplicate', $html);
    }
}
