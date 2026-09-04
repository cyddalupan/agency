<?php

namespace Tests\Feature\BranchUser;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Receivable ("Save Transaction") create page for branch accounts.
 *
 * Receivable routes are role:admin,super_admin,billing — a branch-locked
 * billing user (branch_id > 0, non-admin) may file receivables but must
 * only be offered agents from their OWN branch (plus main-office agents
 * with no branch). Server-side store() must reject other-branch agents.
 */
class BranchUserReceivableCreateTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Branch $branchA;
    private Branch $branchB;
    private User $billingBranchA; // branch-locked billing
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->branchA = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'CEBU Branch']);
        $this->branchB = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'MAKATI Branch']);

        $this->billingBranchA = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
            'branch_id' => $this->branchA->id,
        ]);
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function agentInBranch(Branch $branch, string $name): Agent
    {
        return Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $branch->id,
            'status'    => 'active',
            'name'      => $name,
        ]);
    }

    // ---------- Create page scoping ----------

    #[Test]
    public function branch_locked_billing_receivable_create_only_offers_own_branch_and_main_office_agents(): void
    {
        $own = $this->agentInBranch($this->branchA, 'Agent Own Branch');
        $other = $this->agentInBranch($this->branchB, 'Agent Other Branch');
        $mainOffice = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => null,
            'status'    => 'active',
            'name'      => 'Agent Main Office',
        ]);
        // Applicants belong to their agent; other-branch applicant must not leak in
        Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $other->id]);

        $html = $this->actingAs($this->billingBranchA)
            ->get(route('receivable.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Agent Own Branch', $html);
        $this->assertStringContainsString('Agent Main Office', $html);
        $this->assertStringNotContainsString('Agent Other Branch', $html);

        // Label no longer promises "all branches" to a branch account.
        $this->assertStringNotContainsString('(all branches)', $html);
    }

    #[Test]
    public function admin_receivable_create_still_lists_agents_from_all_branches(): void
    {
        $this->agentInBranch($this->branchA, 'Agent Own Branch');
        $this->agentInBranch($this->branchB, 'Agent Other Branch');

        $html = $this->actingAs($this->admin)
            ->get(route('receivable.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Agent Own Branch', $html);
        $this->assertStringContainsString('Agent Other Branch', $html);
    }

    // ---------- Store enforcement ----------

    #[Test]
    public function branch_locked_billing_cannot_store_receivable_for_other_branch_agent(): void
    {
        $other = $this->agentInBranch($this->branchB, 'Agent Other Branch');

        $this->actingAs($this->billingBranchA)
            ->post(route('receivable.store'), [
                'date'     => now()->toDateString(),
                'agent_id' => $other->id,
                'amount'   => 25000.00,
            ])
            ->assertSessionHasErrors('agent_id')
            ->assertRedirect();

        $this->assertDatabaseCount('receivables', 0);
    }

    #[Test]
    public function branch_locked_billing_can_store_receivable_for_own_branch_agent(): void
    {
        $own = $this->agentInBranch($this->branchA, 'Agent Own Branch');
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $own->id]);

        $this->actingAs($this->billingBranchA)
            ->post(route('receivable.store'), [
                'date'         => now()->toDateString(),
                'agent_id'     => $own->id,
                'applicant_id' => $applicant->id,
                'amount'       => 12000.00,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('receivables', [
            'agency_id' => $this->agency->id,
            'agent_id'  => $own->id,
            'amount'    => 12000.00,
        ]);
    }

    #[Test]
    public function branch_locked_billing_can_store_receivable_for_main_office_agent(): void
    {
        $mainOffice = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => null,
            'status'    => 'active',
            'name'      => 'Agent Main Office',
        ]);

        $this->actingAs($this->billingBranchA)
            ->post(route('receivable.store'), [
                'date'     => now()->toDateString(),
                'agent_id' => $mainOffice->id,
                'amount'   => 5000.00,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('receivables', [
            'agency_id' => $this->agency->id,
            'agent_id'  => $mainOffice->id,
            'amount'    => 5000.00,
        ]);
    }
}
