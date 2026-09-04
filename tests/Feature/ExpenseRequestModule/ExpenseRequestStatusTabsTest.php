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
 * Toybits 2026-08-18 — Status tabs above the expense-request index table.
 *
 * The tabs group requests by payment status (Pending / Approved /
 * For Releasing / Released / Cancelled) so admins can sort at a glance.
 * Clicking a tab filters the table to that status; the default view shows
 * everything.
 */
class ExpenseRequestStatusTabsTest extends TestCase
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

    private function setStatus(ExpenseRequest $request, string $status): void
    {
        $this->actingAs($this->user)
            ->patch(route('expense_request.status', $request), ['status' => $status]);
    }

    #[Test]
    public function index_page_renders_a_tab_for_every_status(): void
    {
        $this->createRequest();

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->getContent();

        // The tab bar exists and every status has a filter link.
        $this->assertStringContainsString('status-tabs', $html);
        foreach (ExpenseRequest::STATUSES as $status) {
            $this->assertStringContainsString('?status=' . $status, $html);
        }
    }

    #[Test]
    public function default_view_shows_requests_of_every_status(): void
    {
        $pending = $this->createRequest(100.00);
        $approved = $this->createRequest(200.00);
        $cancelled = $this->createRequest(300.00);
        $this->setStatus($approved, 'approved');
        $this->setStatus($cancelled, 'cancelled');

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('>'.$pending->reference_no.'<', $html);
        $this->assertStringContainsString('>'.$approved->reference_no.'<', $html);
        $this->assertStringContainsString('>'.$cancelled->reference_no.'<', $html);
    }

    #[Test]
    public function pending_tab_filters_the_table_to_pending_requests(): void
    {
        $pending = $this->createRequest(100.00);
        $approved = $this->createRequest(200.00);
        $this->setStatus($approved, 'approved');

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'pending']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('>'.$pending->reference_no.'<', $html);
        $this->assertStringNotContainsString('>'.$approved->reference_no.'<', $html);
    }

    #[Test]
    public function released_tab_filters_the_table_to_released_requests(): void
    {
        $pending = $this->createRequest(100.00);
        $released = $this->createRequest(200.00);
        $this->setStatus($released, 'released');

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'released']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('>'.$released->reference_no.'<', $html);
        $this->assertStringNotContainsString('>'.$pending->reference_no.'<', $html);
    }

    #[Test]
    public function cancelled_tab_filters_the_table_to_cancelled_requests(): void
    {
        $pending = $this->createRequest(100.00);
        $cancelled = $this->createRequest(200.00);
        $this->setStatus($cancelled, 'cancelled');

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'cancelled']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('>'.$cancelled->reference_no.'<', $html);
        $this->assertStringNotContainsString('>'.$pending->reference_no.'<', $html);
    }

    #[Test]
    public function tabs_show_a_count_badge_per_status(): void
    {
        $this->createRequest(100.00); // pending
        $this->createRequest(200.00); // pending
        $released = $this->createRequest(300.00);
        $this->setStatus($released, 'released');

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->getContent();

        // Pending tab carries count 2, Released carries 1.
        $this->assertStringContainsString('Pending', $html);
        $this->assertMatchesRegularExpression('/Pending\s*<span class="badge[^"]*"[^>]*>2<\/span>/', $html);
        $this->assertMatchesRegularExpression('/Released\s*<span class="badge[^"]*"[^>]*>1<\/span>/', $html);
    }

    #[Test]
    public function invalid_status_falls_back_to_showing_everything(): void
    {
        $pending = $this->createRequest(100.00);
        $approved = $this->createRequest(200.00);
        $this->setStatus($approved, 'approved');

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.index', ['status' => 'bogus']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('>'.$pending->reference_no.'<', $html);
        $this->assertStringContainsString('>'.$approved->reference_no.'<', $html);
    }
}
