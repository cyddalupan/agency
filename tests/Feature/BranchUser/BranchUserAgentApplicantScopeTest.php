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
 * "Transaction - Agent & Applicants view" (Cyd 2026-10-23)
 *
 * 1. Branch accounts (except head office) only see their own branch's agents —
 *    and applicants follow their agent, so an applicant only shows under the
 *    agent it belongs to.
 * 2. Agent Ledger lists only the branch's agents.
 * 3. Admin/Accounting see overall receivable entries; everyone else only
 *    sees what they created.
 */
class BranchUserAgentApplicantScopeTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Branch $branchA;
    private Branch $branchB;
    private Branch $mainBranch;
    private User $billingA;

    protected function setUp(): void
    {
        parent::setUp();

        config(['mjolnir.branch_restrictions' => true]);

        // `agent_report` is only offered on gulf/finas/csdi
        // (ModuleAccess::AGENT_REPORT_AGENCIES); a random-subdomain agency
        // 403s the Agent Ledger regardless of who is signed in.
        $this->agency = Agency::factory()->create(['subdomain' => 'gulf', 'name' => 'Gulf Agency']);
        $this->branchA = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'BACOLOD']);
        $this->branchB = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'DAVAO']);
        $this->mainBranch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'mAIN BRANCH']);

        // Accounting keeps the full picture on its own: seesAllAccountingData()
        // returns true for billing, and the receivable screens do not read
        // isPrivileged(). The privileged() state is kept because that is what the
        // real Accounting accounts look like (MAE/EVELYN are billing users), but it
        // is not load-bearing for this class — the real fix was the agency subdomain
        // below, since agent_report is offered on gulf/finas/csdi only.
        $this->billingA = User::factory()->privileged()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
            'branch_id' => $this->branchA->id,
        ]);
    }

    private function agent(?Branch $branch, string $name): Agent
    {
        return Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $branch?->id,
            'name'      => $name,
            'status'    => 'active',
        ]);
    }

    private function applicant(Agent $agent, string $lastName): Applicant
    {
        return Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'agent_id'   => $agent->id,
            'first_name' => 'Juan',
            'last_name'  => $lastName,
        ]);
    }

    // ---------- 1. Save Transaction: Agent + Applicant pickers ----------

    #[Test]
    public function branch_account_only_offers_applicants_of_its_own_agents(): void
    {
        $own = $this->agent($this->branchA, 'Agent Own');
        $other = $this->agent($this->branchB, 'Agent Other');

        $this->applicant($own, 'ApplicantOwnBranch');
        $this->applicant($other, 'ApplicantOtherBranch');

        $html = $this->actingAs($this->billingA)
            ->get(route('receivable.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('ApplicantOwnBranch', $html);
        $this->assertStringNotContainsString('ApplicantOtherBranch', $html);
        // Agent list stays scoped too.
        $this->assertStringContainsString('Agent Own', $html);
        $this->assertStringNotContainsString('Agent Other', $html);
    }

    #[Test]
    public function branch_account_sees_applicants_of_shared_head_office_agents(): void
    {
        $shared = $this->agent(null, 'Agent Head Office');
        $this->applicant($shared, 'ApplicantHeadOffice');

        $html = $this->actingAs($this->billingA)
            ->get(route('receivable.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('ApplicantHeadOffice', $html);
    }

    #[Test]
    public function head_office_and_admin_still_see_every_applicant(): void
    {
        $other = $this->agent($this->branchB, 'Agent Other');
        $this->applicant($other, 'ApplicantOtherBranch');

        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'branch_id' => null,
        ]);

        $html = $this->actingAs($admin)->get(route('receivable.create'))->assertOk()->getContent();
        $this->assertStringContainsString('ApplicantOtherBranch', $html);
    }

    // ---------- 2. Agent Ledger scoping ----------

    #[Test]
    public function agent_ledger_lists_only_its_own_branch_agents(): void
    {
        $this->agent($this->branchA, 'LedgerOwnAgent');
        $this->agent($this->branchB, 'LedgerOtherAgent');

        $html = $this->actingAs($this->billingA)
            ->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('LedgerOwnAgent', $html);
        $this->assertStringNotContainsString('LedgerOtherAgent', $html);
    }

    #[Test]
    public function branch_account_cannot_open_another_branch_agent_ledger(): void
    {
        $other = $this->agent($this->branchB, 'LedgerOtherAgent');

        $this->actingAs($this->billingA)
            ->get(route('agent_report.show', $other))
            ->assertNotFound();
    }

    #[Test]
    public function branch_account_can_open_its_own_agent_ledger(): void
    {
        $own = $this->agent($this->branchA, 'LedgerOwnAgent');

        $this->actingAs($this->billingA)
            ->get(route('agent_report.show', $own))
            ->assertOk();
    }

    // ---------- Head office naming ----------

    #[Test]
    public function main_branch_named_agency_is_treated_as_head_office(): void
    {
        $headOffice = User::factory()->privileged()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
            'branch_id' => $this->mainBranch->id,
        ]);

        $this->assertTrue($headOffice->isMainOffice());
        $this->assertFalse($headOffice->isBranchLocked(), 'Head office is never branch-restricted.');

        // ... and therefore sees agents from every branch.
        $this->agent($this->branchA, 'LedgerOwnAgent');
        $this->agent($this->branchB, 'LedgerOtherAgent');

        $html = $this->actingAs($headOffice)
            ->get(route('agent_report.index', ['tab' => 'report']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('LedgerOwnAgent', $html);
        $this->assertStringContainsString('LedgerOtherAgent', $html);
    }

    #[Test]
    public function main_office_spelling_is_also_head_office(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Main Office']);
        $user = User::factory()->privileged()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
            'branch_id' => $branch->id,
        ]);

        $this->assertTrue($user->isMainOffice());
    }

    // ---------- 3. Receivable list visibility ----------

    #[Test]
    public function staff_only_sees_the_receivable_entries_they_encoded(): void
    {
        $own = $this->agent($this->branchA, 'Agent Own');

        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'operation',
            'branch_id' => $this->branchA->id,
        ]);
        $colleague = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'operation',
            'branch_id' => $this->branchA->id,
        ]);

        Receivable::create([
            'agency_id' => $this->agency->id,
            'code' => 'AR-OWN',
            'date' => now()->toDateString(),
            'agent_id' => $own->id,
            'amount' => 1000,
            'user_id' => $staff->id,
        ]);
        Receivable::create([
            'agency_id' => $this->agency->id,
            'code' => 'AR-OTHER',
            'date' => now()->toDateString(),
            'agent_id' => $own->id,
            'amount' => 2000,
            'user_id' => $colleague->id,
        ]);

        $html = $this->actingAs($staff)->get(route('receivable.index'))->assertOk()->getContent();
        $this->assertStringContainsString('AR-OWN', $html);
        $this->assertStringNotContainsString('AR-OTHER', $html);
    }

    #[Test]
    public function admin_and_accounting_see_all_receivable_entries(): void
    {
        $own = $this->agent($this->branchA, 'Agent Own');
        $colleague = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'operation',
            'branch_id' => $this->branchA->id,
        ]);

        Receivable::create([
            'agency_id' => $this->agency->id,
            'code' => 'AR-OTHER',
            'date' => now()->toDateString(),
            'agent_id' => $own->id,
            'amount' => 2000,
            'user_id' => $colleague->id,
        ]);

        foreach (['admin', 'billing'] as $type) {
            $user = User::factory()->create([
                'agency_id' => $this->agency->id,
                'user_type' => $type,
                'branch_id' => null,
            ]);

            $html = $this->actingAs($user)->get(route('receivable.index'))->assertOk()->getContent();
            $this->assertStringContainsString('AR-OTHER', $html, "{$type} must see overall receivable entries.");
        }
    }
}
