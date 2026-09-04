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
 * Toybits 2026-08-18 — Full status workflow on the expense-request module:
 *
 *   Pending -> Approved -> For Releasing -> Released   (+ Cancelled as rejection)
 *
 * 1. The show page must expose a "Status / Transaction History" timeline that
 *    shows WHO encoded each change (the actor), continuously updated.
 * 2. The status dropdown lists all five options; the old "Received" status is
 *    replaced by Approved / For Releasing / Released.
 * 3. The index summary + badges reflect the new statuses; cancelled stays
 *    excluded from totals (rejected transactions count toward nothing).
 */
class ExpenseRequestStatusWorkflowTest extends TestCase
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

        return ExpenseRequest::latest('id')->firstOrFail();
    }

    #[Test]
    public function show_page_lists_all_five_status_options(): void
    {
        $request = $this->createRequest();

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.show', $request))
            ->assertOk()
            ->getContent();

        foreach (['pending', 'approved', 'for_releasing', 'released', 'cancelled'] as $status) {
            $this->assertStringContainsString('value="' . $status . '"', $html);
        }
        $this->assertStringNotContainsString('value="received"', $html);
    }

    #[Test]
    public function admin_can_advance_request_through_the_full_workflow(): void
    {
        $request = $this->createRequest();

        $this->actingAs($this->user)
            ->patch(route('expense_request.status', $request), ['status' => 'approved'])
            ->assertRedirect();
        $this->assertSame('approved', $request->fresh()->status);

        $this->actingAs($this->user)
            ->patch(route('expense_request.status', $request), ['status' => 'for_releasing'])
            ->assertRedirect();
        $this->assertSame('for_releasing', $request->fresh()->status);

        $this->actingAs($this->user)
            ->patch(route('expense_request.status', $request), ['status' => 'released'])
            ->assertRedirect();
        $this->assertSame('released', $request->fresh()->status);

        // Every step is logged with the actor (who encoded it).
        $this->assertDatabaseHas('expense_request_histories', [
            'expense_request_id' => $request->id,
            'user_id'            => $this->user->id,
            'from_status'        => 'pending',
            'to_status'          => 'approved',
        ]);
        $this->assertDatabaseHas('expense_request_histories', [
            'expense_request_id' => $request->id,
            'user_id'            => $this->user->id,
            'from_status'        => 'approved',
            'to_status'          => 'for_releasing',
        ]);
        $this->assertDatabaseHas('expense_request_histories', [
            'expense_request_id' => $request->id,
            'user_id'            => $this->user->id,
            'from_status'        => 'for_releasing',
            'to_status'          => 'released',
        ]);
    }

    #[Test]
    public function show_page_displays_status_transaction_history_with_the_encoder(): void
    {
        $request = $this->createRequest();

        $this->actingAs($this->user)
            ->patch(route('expense_request.status', $request), ['status' => 'approved', 'note' => 'Docs verified']);

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.show', $request))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Status / Transaction History', $html);
        $this->assertStringContainsString($this->user->name, $html);
        $this->assertStringContainsString('Docs verified', $html);
    }

    #[Test]
    public function creation_is_logged_as_the_first_history_entry_with_the_encoder(): void
    {
        $request = $this->createRequest();

        $this->assertDatabaseHas('expense_request_histories', [
            'expense_request_id' => $request->id,
            'user_id'            => $this->user->id,
            'from_status'        => null,
            'to_status'          => 'pending',
        ]);
    }

    #[Test]
    public function index_shows_a_badge_for_each_new_status(): void
    {
        $released = $this->createRequest(100.00);
        $approved = $this->createRequest(200.00);
        $forReleasing = $this->createRequest(300.00);
        $cancelled = $this->createRequest(400.00);

        foreach ([
            [$released, 'released'],
            [$approved, 'approved'],
            [$forReleasing, 'for_releasing'],
            [$cancelled, 'cancelled'],
        ] as [$model, $status]) {
            $this->actingAs($this->user)
                ->patch(route('expense_request.status', $model), ['status' => $status]);
        }

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('badge-success', $html); // released
        $this->assertStringContainsString('badge-info', $html);    // approved
        $this->assertStringContainsString('badge-primary', $html); // for releasing
        $this->assertStringContainsString('badge-error', $html);   // cancelled
    }

    #[Test]
    public function index_summary_splits_new_statuses_and_excludes_cancelled(): void
    {
        $this->createRequest(1000.00);                        // pending
        $this->createRequest(500.00);                         // approved
        $this->createRequest(300.00);                         // for releasing
        $this->createRequest(200.00);                         // released

        $cancelled = $this->createRequest(400.00);
        $this->actingAs($this->user)
            ->patch(route('expense_request.status', $cancelled), ['status' => 'cancelled']);

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->getContent();

        // Combined total covers the four active statuses (2,000), not the cancelled 400.
        $this->assertStringContainsString('₱2,000.00', $html);
        $this->assertStringNotContainsString('₱2,400.00', $html);
        $this->assertStringContainsString('Approved', $html);
        $this->assertStringContainsString('For Releasing', $html);
        $this->assertStringContainsString('Released', $html);
    }
}
