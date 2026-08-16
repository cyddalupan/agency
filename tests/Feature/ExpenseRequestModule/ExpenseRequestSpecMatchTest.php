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

/**
 * MATCHES checklist item — verify the remaining spec aspects have TDD coverage:
 * branch-scoped agent, applicant-under-agent, and the two-level Main->Sub picker
 * on the create page (EVIDENCE: Tab 2 dropdown shows SUBs under selected Main only).
 */
class ExpenseRequestSpecMatchTest extends TestCase
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

    // ---------- Branch-scoped agent ----------

    #[Test]
    public function store_rejects_agent_from_another_branch(): void
    {
        $branchA = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        $branchB = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Cebu']);
        $agentB = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branchB->id]);
        $country = Country::factory()->create();
        [$main, $sub] = $this->mainAndSub('agent');

        $payload = $this->payload($branchA, $country, $main, $sub);
        $payload['lines'][0]['agent_id'] = $agentB->id; // agent of Cebu, request branch = Manila

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $payload)
            ->assertSessionHasErrors('lines.*.agent_id');

        $this->assertDatabaseCount('expense_requests', 0);
    }

    #[Test]
    public function store_accepts_agent_from_selected_branch(): void
    {
        $branchA = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        $agentA = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branchA->id]);
        $country = Country::factory()->create();
        [$main, $sub] = $this->mainAndSub('agent');

        $payload = $this->payload($branchA, $country, $main, $sub);
        $payload['lines'][0]['agent_id'] = $agentA->id;

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseCount('expense_requests', 1);
        $this->assertSame($agentA->id, (int) ExpenseRequest::first()->items->first()->agent_id);
    }

    // ---------- Applicant under the selected agent ----------

    #[Test]
    public function store_rejects_applicant_under_a_different_agent(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $agentA = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branch->id]);
        $agentB = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branch->id]);
        $applicantOfB = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agentB->id]);
        $country = Country::factory()->create();
        [$main, $sub] = $this->mainAndSub('agent');

        $payload = $this->payload($branch, $country, $main, $sub);
        $payload['lines'][0]['agent_id'] = $agentA->id;
        $payload['lines'][0]['applicant_id'] = $applicantOfB->id; // applicant belongs to agentB

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $payload)
            ->assertSessionHasErrors('lines.*.applicant_id');

        $this->assertDatabaseCount('expense_requests', 0);
    }

    #[Test]
    public function store_accepts_applicant_under_the_selected_agent(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branch->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);
        $country = Country::factory()->create();
        [$main, $sub] = $this->mainAndSub('agent');

        $payload = $this->payload($branch, $country, $main, $sub);
        $payload['lines'][0]['agent_id'] = $agent->id;
        $payload['lines'][0]['applicant_id'] = $applicant->id;

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseCount('expense_requests', 1);
        $item = ExpenseRequest::first()->items->first();
        $this->assertSame($agent->id, (int) $item->agent_id);
        $this->assertSame($applicant->id, (int) $item->applicant_id);
    }

    // ---------- EVIDENCE: create page offers Main Accounts as the single picker ----------

    #[Test]
    public function create_page_offers_mains_as_the_single_account_picker(): void
    {
        $mainOffice = Account::factory()->create([
            'agency_id' => $this->agency->id, 'parent_id' => null,
            'name' => 'Office Expenses', 'type' => 'expense', 'charge_type' => 'office',
        ]);
        $subOffice = Account::factory()->create([
            'agency_id' => $this->agency->id, 'parent_id' => $mainOffice->id,
            'name' => 'Salaries', 'type' => 'expense', 'charge_type' => 'office',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('expense_request.create'))
            ->assertOk();

        $response->assertViewHas('mains');
        $mains = $response->viewData('mains');
        $this->assertTrue($mains->contains('id', $mainOffice->id));

        // The single Main Account picker lists Mains with their charge offset...
        $html = $response->getContent();
        $this->assertStringContainsString('data-main-group="1"', $html);
        $this->assertStringContainsString('data-offset="' . $mainOffice->charge_type . '"', $html);

        // ...and the sub-account picker (account_id / data-main cascade) is gone.
        $this->assertStringNotContainsString('lines[0][account_id]', $html);
        $this->assertStringNotContainsString('data-main="', $html);
    }

    // ---------- Helpers ----------

    private function mainAndSub(string $charge): array
    {
        $main = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => $charge === 'office' ? 'Office Expenses' : 'Agent Accounts',
            'type'        => 'expense',
            'charge_type' => $charge,
        ]);

        $sub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $main->id,
            'name'        => $charge === 'office' ? 'Salaries' : 'Agent Advances',
            'type'        => 'expense',
            'charge_type' => $charge,
        ]);

        return [$main, $sub];
    }

    private function payload(Branch $branch, Country $country, Account $main, Account $sub): array
    {
        return [
            'branch_id' => $branch->id,
            'notes'     => null,
            'lines'     => [
                [
                    'charge'          => $sub->charge_type,
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
        ];
    }
}
