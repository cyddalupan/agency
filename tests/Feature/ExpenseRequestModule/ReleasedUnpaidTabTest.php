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
 * Card 6af1719c — "Expenses transaction 'Wrong post'".
 *
 * Josh (2026-11-09): "these should be posted on the Expense and Payments module
 * Pending tab", pointing at released Fit-to-work / Deployed commission rows
 * whose payment is still 0.00.
 *
 * The client data is the source of truth, so this is a reporting view: the
 * "Released · Unpaid" tab must surface those requests WITHOUT changing any
 * stored status.
 */
class ReleasedUnpaidTabTest extends TestCase
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

    private function makeAgent(): Agent
    {
        return Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
        ]);
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

        $agent = $this->makeAgent();

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
    public function released_unpaid_commission_appears_on_the_unpaid_release_tab(): void
    {
        $er = $this->makeRequest(
            ExpenseRequest::STATUS_RELEASED,
            25000.00,
            0.00,
            'Fit to work',
            'agent',
            'RU-1'
        );

        $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'unpaid_release']))
            ->assertOk()
            ->assertSee('RU-1');
    }

    #[Test]
    public function fully_paid_released_commission_is_not_listed(): void
    {
        $this->makeRequest(
            ExpenseRequest::STATUS_RELEASED,
            25000.00,
            25000.00,
            'Fit to work',
            'agent',
            'RU-PAID'
        );

        $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'unpaid_release']))
            ->assertOk()
            ->assertDontSee('RU-PAID');
    }

    #[Test]
    public function pending_requests_are_not_pulled_into_the_unpaid_release_tab(): void
    {
        $this->makeRequest(
            ExpenseRequest::STATUS_PENDING,
            5000.00,
            0.00,
            'Fit to work',
            'agent',
            'RU-PENDING'
        );

        $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'unpaid_release']))
            ->assertOk()
            ->assertDontSee('RU-PENDING');
    }

    #[Test]
    public function office_charged_released_requests_are_not_listed(): void
    {
        $this->makeRequest(
            ExpenseRequest::STATUS_RELEASED,
            9000.00,
            0.00,
            'Fit to work',
            'office',
            'RU-OFFICE'
        );

        $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'unpaid_release']))
            ->assertOk()
            ->assertDontSee('RU-OFFICE');
    }

    #[Test]
    public function the_tab_does_not_change_any_stored_status(): void
    {
        $er = $this->makeRequest(
            ExpenseRequest::STATUS_RELEASED,
            25000.00,
            0.00,
            'Deployed',
            'agent',
            'RU-NOMUTATE'
        );

        $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'unpaid_release']))
            ->assertOk();

        // The client's data must be untouched by viewing it.
        $this->assertSame(
            ExpenseRequest::STATUS_RELEASED,
            $er->fresh()->status,
            'Viewing the tab must not re-status the request'
        );
    }

    #[Test]
    public function the_normal_status_tabs_still_work(): void
    {
        $this->makeRequest(
            ExpenseRequest::STATUS_PENDING,
            5000.00,
            0.00,
            'Fit to work',
            'agent',
            'RU-NORMAL-PENDING'
        );

        $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('RU-NORMAL-PENDING');
    }
}
