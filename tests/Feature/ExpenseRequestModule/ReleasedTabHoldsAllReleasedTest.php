<?php

namespace Tests\Feature\ExpenseRequestModule;

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
 * Card 6af431cc — 'LANDAS: RELEASED - UNPAID "Remove"'.
 *
 * Josh (2026-11-11): "RELEASED-UNPAID: Remove this tab. All transactions with
 * Released status will be posted at the Released Tab."
 *
 * Card 6af1719c had added a separate "Released · Unpaid" tab to surface
 * released commission lines that were still owed. Josh has now told us that
 * second tab is redundant: everything released belongs on Released.
 *
 * These tests pin that down — the Released tab must carry the previously
 * "unpaid" rows, and the extra tab must not exist any more.
 */
class ReleasedTabHoldsAllReleasedTest extends TestCase
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
            'user_type' => 'super_admin',
        ]);

        Cache::put('fx_usd_to_php', 56.0, 3600);
    }

    private function makeRequest(
        string $status,
        float $amount,
        float $payment,
        string $accountName = 'Fit to work',
        string $charge = 'agent',
        ?string $referenceNo = null
    ): ExpenseRequest {
        $er = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'reference_no' => $referenceNo ?? (string) random_int(100000, 999999),
            'date'         => now()->toDateString(),
            'status'       => $status,
            'branch_id'    => $this->branch->id,
            'notes'        => 'test',
        ]);

        $acct = Account::firstOrCreate(
            ['agency_id' => $this->agency->id, 'name' => $accountName],
            ['type' => 'expense', 'charge_type' => 'agent', 'parent_id' => null]
        );

        $agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
        ]);

        $applicant = Applicant::factory()->withStatus(0)->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $agent->id,
        ]);

        ExpenseRequestItem::create([
            'expense_request_id' => $er->id,
            'charge'             => $charge,
            'agent_id'           => $charge === 'agent' ? $agent->id : null,
            'applicant_id'       => $applicant->id,
            'country_id'         => Country::factory()->create()->id,
            'currency'           => 'PHP',
            'amount'             => $amount,
            'payment'            => $payment,
            'account_id'         => $acct->id,
            'particular'         => 'test',
        ]);

        return $er;
    }

    #[Test]
    public function the_released_tab_lists_released_commission_that_is_still_unpaid(): void
    {
        $this->makeRequest(ExpenseRequest::STATUS_RELEASED, 25000.00, 0.00, 'Fit to work', 'agent', 'RU-1');

        $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'released']))
            ->assertOk()
            ->assertSee('RU-1');
    }

    #[Test]
    public function the_released_tab_also_lists_fully_paid_released_rows(): void
    {
        $this->makeRequest(ExpenseRequest::STATUS_RELEASED, 25000.00, 25000.00, 'Fit to work', 'agent', 'RU-PAID');

        $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'released']))
            ->assertOk()
            ->assertSee('RU-PAID');
    }

    #[Test]
    public function the_released_tab_lists_office_charged_released_rows(): void
    {
        $this->makeRequest(ExpenseRequest::STATUS_RELEASED, 9000.00, 0.00, 'Fit to work', 'office', 'RU-OFFICE');

        $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'released']))
            ->assertOk()
            ->assertSee('RU-OFFICE');
    }

    #[Test]
    public function the_released_tab_does_not_pull_in_pending_rows(): void
    {
        $this->makeRequest(ExpenseRequest::STATUS_PENDING, 5000.00, 0.00, 'Fit to work', 'agent', 'RU-PENDING');

        $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'released']))
            ->assertOk()
            ->assertDontSee('RU-PENDING');
    }

    #[Test]
    public function the_removed_unpaid_release_tab_is_no_longer_rendered(): void
    {
        $this->makeRequest(ExpenseRequest::STATUS_RELEASED, 25000.00, 0.00, 'Fit to work', 'agent', 'RU-GONE');

        $response = $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'released']))
            ->assertOk();

        // The tab and its badge label must be gone from the page.
        $response->assertDontSee('Released · Unpaid');
        $response->assertDontSee('unpaid_release');
    }

    #[Test]
    public function a_stale_unpaid_release_link_falls_back_to_the_all_tab(): void
    {
        // Old bookmarks / emails may still carry ?status=unpaid_release.
        // It is not a real status, so the page must still render cleanly.
        $this->makeRequest(ExpenseRequest::STATUS_RELEASED, 25000.00, 0.00, 'Fit to work', 'agent', 'RU-STALE');

        $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'unpaid_release']))
            ->assertOk()
            ->assertSee('RU-STALE');
    }

    #[Test]
    public function viewing_the_released_tab_does_not_change_any_stored_status(): void
    {
        $er = $this->makeRequest(ExpenseRequest::STATUS_RELEASED, 25000.00, 0.00, 'Deployed', 'agent', 'RU-NOMUTATE');

        $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'released']))
            ->assertOk();

        $this->assertSame(ExpenseRequest::STATUS_RELEASED, $er->fresh()->status);
    }
}
