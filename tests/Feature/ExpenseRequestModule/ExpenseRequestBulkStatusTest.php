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
 * Toybits 2026-08-31 — Batch status update on the expense-request index:
 *
 * 1. Admin can select multiple requests (checkboxes) and change them to one
 *    status in a single submit; each changed request gets its own history entry.
 * 2. Requests already in the target status are skipped, and only the caller's
 *    own agency's requests are ever touched (isolation).
 * 3. Non-admin (staff/billing) cannot use the batch endpoint.
 * 4. The index page exposes the checkboxes + bulk toolbar to admin only.
 */
class ExpenseRequestBulkStatusTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private User $billing;
    private User $staff;
    private Branch $branch;
    private Country $country;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);
        $this->staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $this->country = Country::factory()->create();
    }

    /**
     * Create one expense request through the real store route, for the given
     * agency/user (defaults to this agency + admin). Returns the created request.
     */
    private function createRequest(float $amount = 1000.00, ?Agency $agency = null, ?User $actor = null): ExpenseRequest
    {
        $agency = $agency ?? $this->agency;
        $actor  = $actor  ?? $this->admin;

        $branch = Branch::factory()->create(['agency_id' => $agency->id]);
        $country = Country::factory()->create();

        $account = Account::factory()->create(['agency_id' => $agency->id, 'charge_type' => 'agent']);
        $sub = Account::factory()->create([
            'agency_id'   => $agency->id,
            'parent_id'   => $account->id,
            'charge_type' => 'agent',
        ]);

        $agent = Agent::factory()->create(['agency_id' => $agency->id, 'branch_id' => $branch->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $agency->id, 'agent_id' => $agent->id]);

        $this->actingAs($actor)
            ->post(route('expense_request.store'), [
                'branch_id' => $branch->id,
                'lines'     => [
                    [
                        'charge'          => 'agent',
                        'sub_account_id'  => $sub->id,
                        'agent_id'        => $agent->id,
                        'applicant_id'    => $applicant->id,
                        'country_id'      => $country->id,
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
    public function admin_can_batch_approve_multiple_requests_with_history_per_request(): void
    {
        $first = $this->createRequest();
        $second = $this->createRequest(2000.00);

        $this->actingAs($this->admin)
            ->post(route('expense_request.bulk_status'), [
                'ids'    => [$first->id, $second->id],
                'status' => 'approved',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('approved', $first->fresh()->status);
        $this->assertSame('approved', $second->fresh()->status);

        foreach ([$first, $second] as $request) {
            $this->assertDatabaseHas('expense_request_histories', [
                'expense_request_id' => $request->id,
                'user_id'            => $this->admin->id,
                'from_status'        => 'pending',
                'to_status'          => 'approved',
            ]);
        }
    }

    #[Test]
    public function requests_already_in_target_status_are_skipped(): void
    {
        $first = $this->createRequest();
        $first->update(['status' => 'approved']);

        $second = $this->createRequest(2000.00);

        $this->actingAs($this->admin)
            ->post(route('expense_request.bulk_status'), [
                'ids'    => [$first->id, $second->id],
                'status' => 'approved',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'Updated 1 expense request(s)'));

        // The already-approved one keeps its original history (no new entry):
        // 2 creation entries (store logs one per request) + 1 bulk entry = 3.
        $this->assertSame('approved', $first->fresh()->status);
        $this->assertSame('approved', $second->fresh()->status);

        $this->assertDatabaseCount('expense_request_histories', 3);
    }

    #[Test]
    public function other_agencies_requests_are_never_touched(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAdmin = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);

        $otherRequest = $this->createRequest(500.00, $otherAgency, $otherAdmin);
        $mine = $this->createRequest();

        $this->actingAs($this->admin)
            ->post(route('expense_request.bulk_status'), [
                'ids'    => [$mine->id, $otherRequest->id],
                'status' => 'approved',
            ])
            ->assertRedirect();

        $this->assertSame('approved', $mine->fresh()->status);
        $this->assertSame('pending', $otherRequest->fresh()->status);
    }

    #[Test]
    public function non_admin_cannot_use_batch_status_endpoint(): void
    {
        $request = $this->createRequest();

        foreach ([$this->staff, $this->billing] as $user) {
            $this->actingAs($user)
                ->post(route('expense_request.bulk_status'), [
                    'ids'    => [$request->id],
                    'status' => 'approved',
                ])
                ->assertForbidden();
        }

        $this->assertSame('pending', $request->fresh()->status);
    }

    #[Test]
    public function index_page_shows_bulk_toolbar_to_admin_but_not_billing(): void
    {
        $this->createRequest();

        $adminHtml = $this->actingAs($this->admin)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('bulk-status-form', $adminHtml);
        $this->assertStringContainsString('request-checkbox', $adminHtml);

        // Billing can view the index (route allows it) but must not get the
        // admin-only batch toolbar.
        $billingHtml = $this->actingAs($this->billing)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('bulk-status-form', $billingHtml);
        $this->assertStringNotContainsString('request-checkbox', $billingHtml);
    }

    #[Test]
    public function batch_status_validates_status_and_ids(): void
    {
        $request = $this->createRequest();

        $this->actingAs($this->admin)
            ->post(route('expense_request.bulk_status'), [
                'ids'    => [$request->id],
                'status' => 'not_a_status',
            ])
            ->assertSessionHasErrors('status');

        $this->actingAs($this->admin)
            ->post(route('expense_request.bulk_status'), [
                'ids'    => [],
                'status' => 'approved',
            ])
            ->assertSessionHasErrors('ids');
    }
}
