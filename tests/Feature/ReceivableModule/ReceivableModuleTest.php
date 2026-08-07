<?php

namespace Tests\Feature\ReceivableModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Receivable;
use App\Models\ReceivableHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReceivableModuleTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    // ---------- Access control ----------

    #[Test]
    public function unauthenticated_user_cannot_access_receivable_module(): void
    {
        $this->get(route('receivable.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function unauthorized_role_cannot_access_receivable_module(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $this->actingAs($staff)
            ->get(route('receivable.index'))
            ->assertForbidden();
    }

    // ---------- Create / code generation ----------

    #[Test]
    public function store_generates_a_six_digit_code_unique_per_agency(): void
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);

        $this->actingAs($this->user)
            ->post(route('receivable.store'), $this->validPayload($agent, $applicant))
            ->assertRedirect();

        $this->assertDatabaseHas('receivables', [
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'agent_id'  => $agent->id,
            'applicant_id' => $applicant->id,
            'status'    => 'pending',
            'amount'    => 25000.00,
        ]);

        $first = Receivable::first();
        $this->assertMatchesRegularExpression('/^\d{6}$/', $first->code);

        // A second receivable gets a different unique code
        $this->actingAs($this->user)
            ->post(route('receivable.store'), $this->validPayload($agent, $applicant, ['amount' => 10000.00]))
            ->assertRedirect();

        $codes = Receivable::pluck('code')->all();
        $this->assertCount(2, $codes);
        $this->assertCount(2, array_unique($codes));
    }

    #[Test]
    public function receivable_defaults_to_pending_status(): void
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);

        $this->actingAs($this->user)
            ->post(route('receivable.store'), $this->validPayload($agent, $applicant))
            ->assertRedirect();

        $this->assertSame('pending', Receivable::first()->status);
    }

    #[Test]
    public function receivable_requires_agent_and_amount(): void
    {
        $this->actingAs($this->user)
            ->post(route('receivable.store'), $this->validPayload(null, null, ['amount' => null]))
            ->assertSessionHasErrors(['agent_id', 'amount']);
    }

    // ---------- Agent -> Applicant cascade ----------

    #[Test]
    public function create_form_offers_all_agents_across_branches(): void
    {
        $branchA = \App\Models\Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Branch A']);
        $branchB = \App\Models\Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Branch B']);
        Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branchA->id, 'name' => 'Agent One']);
        Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branchB->id, 'name' => 'Agent Two']);

        // An agent from another agency must NOT appear
        $otherAgency = Agency::factory()->create();
        Agent::factory()->create(['agency_id' => $otherAgency->id, 'name' => 'Other Agency Agent']);

        $this->actingAs($this->user)
            ->get(route('receivable.create'))
            ->assertOk()
            ->assertViewHas('agents', function ($agents) {
                $names = $agents->pluck('name');
                return $names->contains('Agent One')
                    && $names->contains('Agent Two')
                    && ! $names->contains('Other Agency Agent');
            });
    }

    #[Test]
    public function applicant_must_belong_to_selected_agent(): void
    {
        $agentA = Agent::factory()->create(['agency_id' => $this->agency->id]);
        $agentB = Agent::factory()->create(['agency_id' => $this->agency->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agentB->id]);

        // Request references agent A but an applicant under agent B => rejected
        $this->actingAs($this->user)
            ->post(route('receivable.store'), $this->validPayload($agentA, $applicant))
            ->assertSessionHasErrors('applicant_id');
    }

    #[Test]
    public function applicant_from_other_agency_is_rejected(): void
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id]);
        $otherAgency = Agency::factory()->create();
        $otherApplicant = Applicant::factory()->create(['agency_id' => $otherAgency->id]);

        $this->actingAs($this->user)
            ->post(route('receivable.store'), $this->validPayload($agent, $otherApplicant))
            ->assertSessionHasErrors('applicant_id');
    }

    // ---------- Agency isolation ----------

    #[Test]
    public function index_only_lists_own_agency_receivables(): void
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);
        Receivable::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'agent_id'  => $agent->id,
            'applicant_id' => $applicant->id,
        ]);

        $otherAgency = Agency::factory()->create();
        $otherAgent = Agent::factory()->create(['agency_id' => $otherAgency->id]);
        $otherApplicant = Applicant::factory()->create(['agency_id' => $otherAgency->id, 'agent_id' => $otherAgent->id]);
        $otherReceivable = Receivable::factory()->create([
            'agency_id' => $otherAgency->id,
            'agent_id'  => $otherAgent->id,
            'applicant_id' => $otherApplicant->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('receivable.index'))
            ->assertOk()
            ->assertDontSee($otherReceivable->code);
    }

    #[Test]
    public function user_cannot_view_another_agencys_receivable(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAgent = Agent::factory()->create(['agency_id' => $otherAgency->id]);
        $otherApplicant = Applicant::factory()->create(['agency_id' => $otherAgency->id, 'agent_id' => $otherAgent->id]);
        $otherReceivable = Receivable::factory()->create([
            'agency_id' => $otherAgency->id,
            'agent_id'  => $otherAgent->id,
            'applicant_id' => $otherApplicant->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('receivable.show', $otherReceivable))
            ->assertNotFound();
    }

    // ---------- Admin-only status change + history ----------

    #[Test]
    public function only_admin_can_change_receivable_status(): void
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);
        $receivable = Receivable::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'agent_id'  => $agent->id,
            'applicant_id' => $applicant->id,
            'status'    => 'pending',
        ]);

        $billing = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'billing']);

        // Billing (finance) can view but NOT change status — only admin
        $this->actingAs($billing)
            ->patch(route('receivable.status', $receivable), ['status' => 'received'])
            ->assertForbidden();

        $this->assertSame('pending', $receivable->fresh()->status);
    }

    #[Test]
    public function admin_can_change_status_and_history_is_logged(): void
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);
        $receivable = Receivable::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'agent_id'  => $agent->id,
            'applicant_id' => $applicant->id,
            'status'    => 'pending',
        ]);

        $this->actingAs($this->user)
            ->patch(route('receivable.status', $receivable), ['status' => 'received'])
            ->assertRedirect();

        $this->assertSame('received', $receivable->fresh()->status);

        $this->assertDatabaseHas('receivable_histories', [
            'receivable_id' => $receivable->id,
            'user_id'       => $this->user->id,
            'from_status'   => 'pending',
            'to_status'     => 'received',
        ]);
    }

    #[Test]
    public function receivable_histories_are_exposed_on_review_page(): void
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);
        $receivable = Receivable::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'agent_id'  => $agent->id,
            'applicant_id' => $applicant->id,
            'status'    => 'received',
        ]);
        ReceivableHistory::factory()->create([
            'receivable_id' => $receivable->id,
            'agency_id'     => $this->agency->id,
            'user_id'       => $this->user->id,
            'from_status'   => 'pending',
            'to_status'     => 'received',
        ]);

        $this->actingAs($this->user)
            ->get(route('receivable.show', $receivable))
            ->assertOk()
            ->assertViewHas('history', function ($history) use ($receivable) {
                return $history->count() === 1 && $history->first()->receivable_id === $receivable->id;
            });
    }

    // ---------- Helpers ----------

    private function validPayload(?Agent $agent = null, ?Applicant $applicant = null, array $overrides = []): array
    {
        return array_merge([
            'date'          => now()->toDateString(),
            'ref_ar'        => 'AR-1001',
            'agent_id'      => $agent?->id,
            'applicant_id'  => $applicant?->id,
            'amount'        => 25000.00,
            'account'       => 'Placement Fee',
            'debit_account' => 'Receivable',
            'type'          => 'Full Payment',
            'mode'          => 'GCash',
            'particular'    => 'Placement fee collection',
        ], $overrides);
    }
}
