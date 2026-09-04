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
 * Cyd 2026-08-16 — create page flow: both Office and Agent charges pick
 * Agent first, then Applicant (applicants cascade by the selected agent).
 * Server-side: an office-charged line may carry agent_id + applicant_id
 * (applicant must belong to the selected agent), same as agent charges.
 */
class ExpenseRequestAgentFirstFlowTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $admin;

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
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $this->country = Country::factory()->create();
    }

    private function officeMain(): Account
    {
        return Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Office Expenses',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
    }

    private function applicantMain(): Account
    {
        return Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'APPLICANT',
            'type'        => 'expense',
            'charge_type' => 'applicant',
        ]);
    }

    #[Test]
    public function create_page_always_renders_the_agent_picker(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->getContent();

        // The Agent picker is present in the initial (office-charged) line and
        // is not hidden server-side — there is no agent-only hiding row anymore.
        $this->assertStringNotContainsString('data-agent-row', $html);
        $this->assertStringContainsString('lines[0][agent_id]', $html);
        $this->assertStringContainsString('lines[0][applicant_id]', $html);
    }

    #[Test]
    public function store_accepts_office_charge_with_agent_and_its_applicant(): void
    {
        $agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
        ]);
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $agent->id,
        ]);
        $main = $this->officeMain();
        $applicantMain = $this->applicantMain();
        $applicantSub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $applicantMain->id,
            'type'        => 'expense',
            'charge_type' => 'applicant',
        ]);

        $payload = [
            'branch_id' => $this->branch->id,
            'lines'     => [
                [
                    'charge'         => 'office',
                    'sub_account_id' => $applicantSub->id,
                    'agent_id'       => $agent->id,
                    'applicant_id'   => $applicant->id,
                    'country_id'     => $this->country->id,
                    'currency'       => 'PHP',
                    'amount'         => 500.00,
                    'particular'     => 'Office expense under agent flow',
                ],
            ],
        ];

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $payload)
            ->assertRedirect();

        $item = ExpenseRequest::first()->items->first();
        $this->assertSame($agent->id, (int) $item->agent_id);
        $this->assertSame($applicant->id, (int) $item->applicant_id);
        // Toybits rule: office + applicant -> applicant accounts.
        $this->assertSame($applicantSub->id, (int) $item->account_id);
    }

    #[Test]
    public function store_rejects_office_charge_applicant_under_a_different_agent(): void
    {
        $agentA = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $this->branch->id]);
        $agentB = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $this->branch->id]);
        $applicantOfB = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agentB->id]);
        $this->officeMain();
        $applicantMain = $this->applicantMain(); // office + applicant -> applicant group must resolve first
        $applicantSub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $applicantMain->id,
            'type'        => 'expense',
            'charge_type' => 'applicant',
        ]);

        $payload = [
            'branch_id' => $this->branch->id,
            'lines'     => [
                [
                    'charge'         => 'office',
                    'sub_account_id' => $applicantSub->id,
                    'agent_id'       => $agentA->id,
                    'applicant_id'   => $applicantOfB->id, // belongs to agentB
                    'country_id'     => $this->country->id,
                    'currency'       => 'PHP',
                    'amount'         => 500.00,
                    'particular'     => 'Mismatch',
                ],
            ],
        ];

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $payload)
            ->assertSessionHasErrors('lines.*.applicant_id');

        $this->assertDatabaseCount('expense_requests', 0);
    }
}
