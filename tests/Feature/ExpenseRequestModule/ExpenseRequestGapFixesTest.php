<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseRequestGapFixesTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    // ---------- Gap 1: reference format (5-digit, starts at 2000) ----------

    #[Test]
    public function first_reference_number_starts_at_2000(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        [$main, $sub] = $this->mainAndSub('office');

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $this->payload($branch, $country, $main, $sub))
            ->assertRedirect();

        $this->assertSame('2000', ExpenseRequest::first()->reference_no);
    }

    #[Test]
    public function reference_number_increments_per_agency(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        [$main, $sub] = $this->mainAndSub('office');

        $this->actingAs($this->admin)->post(route('expense_request.store'), $this->payload($branch, $country, $main, $sub))->assertRedirect();
        $this->actingAs($this->admin)->post(route('expense_request.store'), $this->payload($branch, $country, $main, $sub))->assertRedirect();

        $refs = ExpenseRequest::orderBy('id')->pluck('reference_no')->all();
        $this->assertSame(['2000', '2001'], $refs);
    }

    #[Test]
    public function reference_number_is_isolated_between_agencies(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        [$main, $sub] = $this->mainAndSub('office');
        $this->actingAs($this->admin)->post(route('expense_request.store'), $this->payload($branch, $country, $main, $sub))->assertRedirect();

        $other = Agency::factory()->create();
        $otherAdmin = User::factory()->create(['agency_id' => $other->id, 'user_type' => 'admin']);
        $otherBranch = Branch::factory()->create(['agency_id' => $other->id]);
        [$otherMain, $otherSub] = $this->mainAndSub('office', $other);

        $this->actingAs($otherAdmin)
            ->post(route('expense_request.store'), $this->payload($otherBranch, $country, $otherMain, $otherSub))
            ->assertRedirect();

        $this->assertSame('2000', ExpenseRequest::where('agency_id', $other->id)->first()->reference_no);
    }

    // ---------- Gap 2: Main -> Sub account hierarchy ----------

    #[Test]
    public function store_uses_selected_sub_account_as_the_item_account(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        [$main, $sub] = $this->mainAndSub('office');

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $this->payload($branch, $country, $main, $sub))
            ->assertRedirect();

        $this->assertDatabaseCount('expense_requests', 1);
        $item = ExpenseRequest::first()->items->first();
        // The item's account IS the selected Sub account.
        $this->assertSame($sub->id, (int) $item->account_id);
    }

    #[Test]
    public function store_ignores_a_stray_account_id_in_the_payload(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        [$mainA, $subA] = $this->mainAndSub('office');
        [, $subB] = $this->mainAndSub('office');

        $payload = $this->payload($branch, $country, $mainA, $subA);
        // A stray account_id (from the removed sub-account field) is ignored.
        $payload['lines'][0]['account_id'] = $subB->id;

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $payload)
            ->assertRedirect();

        $item = ExpenseRequest::first()->items->first();
        $this->assertSame($subA->id, (int) $item->account_id);
    }

    #[Test]
    public function store_rejects_sub_account_as_main_account(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        [$main, $sub] = $this->mainAndSub('office');

        $payload = $this->payload($branch, $country, $main, $sub);
        // main_account_id points at a SUB account -> invalid
        $payload['lines'][0]['main_account_id'] = $sub->id;

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $payload)
            ->assertSessionHasErrors('lines.0.main_account_id');

        $this->assertDatabaseCount('expense_requests', 0);
    }

    #[Test]
    public function store_keeps_charge_gating_on_main_accounts(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        $agentMain = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Agent Accounts',
            'type'        => 'expense',
            'charge_type' => 'agent',
        ]);

        $payload = $this->payload($branch, $country, $agentMain, $agentMain);
        $payload['lines'][0]['charge'] = 'office'; // agent Main with office charge -> invalid

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $payload)
            ->assertSessionHasErrors('lines.0.main_account_id');

        $this->assertDatabaseCount('expense_requests', 0);
    }

    // ---------- Gap 3: billing can view, admin-only status change ----------

    #[Test]
    public function billing_can_view_index_and_show(): void
    {
        $billing = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'billing']);
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        [$main, $sub] = $this->mainAndSub('office');
        $this->actingAs($this->admin)->post(route('expense_request.store'), $this->payload($branch, $country, $main, $sub));

        $request = ExpenseRequest::first();

        $this->actingAs($billing)->get(route('expense_request.index'))->assertOk();
        $this->actingAs($billing)->get(route('expense_request.show', $request))->assertOk();
    }

    #[Test]
    public function billing_cannot_change_status(): void
    {
        $billing = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'billing']);
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        [$main, $sub] = $this->mainAndSub('office');
        $this->actingAs($this->admin)->post(route('expense_request.store'), $this->payload($branch, $country, $main, $sub));

        $request = ExpenseRequest::first();

        $this->actingAs($billing)
            ->patch(route('expense_request.status', $request), ['status' => 'approved'])
            ->assertForbidden();

        $this->assertSame('pending', $request->fresh()->status);
    }

    // ---------- Helpers ----------

    private function mainAndSub(string $charge, ?Agency $agency = null): array
    {
        $agency = $agency ?? $this->agency;

        $main = Account::factory()->create([
            'agency_id'   => $agency->id,
            'parent_id'   => null,
            'name'        => $charge === 'office' ? 'Office Expenses' : 'Agent Accounts',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);

        $sub = Account::factory()->create([
            'agency_id'   => $agency->id,
            'parent_id'   => $main->id,
            'name'        => $charge === 'office' ? 'Salaries' : 'Agent Advances',
            'type'        => 'expense',
            'charge_type' => $charge,
        ]);

        return [$main, $sub];
    }

    private function payload(Branch $branch, Country $country, Account $main, Account $sub, array $overrides = []): array
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branch->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);

        return array_merge([
            'branch_id' => $branch->id,
            'notes'     => null,
            'lines'     => [
                [
                    'charge'          => $sub->charge_type,
                    'sub_account_id'  => $sub->id,
                    'main_account_id' => $main->id,
                    'agent_id'        => null,
                    'applicant_id'    => null,
                    'country_id'      => $country->id,
                    'currency'        => 'PHP',
                    'amount'          => 1000.00,
                    'account_id'      => $sub->id,
                    'particular'      => 'Advance',
                ],
            ],
        ], $overrides);
    }
}
