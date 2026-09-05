<?php

namespace Tests\Feature\BranchUser;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\User;
use App\Models\Account;
use App\Models\ExpenseRequest;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * "Branch user" add/edit pages (Mjolnir: branch user types).
 *
 * Branch users (accounts bound to a branch — branch_id > 0 and not
 * super_admin, see User::isBranchLocked) must NOT see a branch dropdown
 * on add/edit pages: the branch is hidden and automatically set from
 * their own branch. An admin with a branch is a branch account. Only
 * branch-less accounts (main office) and super_admin may still pick.
 *
 * Pages in scope: Applicant create/edit, Expense Request create.
 */
class BranchUserBranchFieldAddEditTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Branch $branchA;
    private Branch $branchB;
    private User $staffBranchA;   // branch-locked staff (applicant module)
    private User $billingBranchA; // branch-locked billing (expense module)
    private User $admin;          // main-office admin (no branch) — dropdown stays
    private User $adminBranchA;   // admin WITH a branch — branch account, locked

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->branchA = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'CEBU Branch']);
        $this->branchB = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'MAKATI Branch']);

        $this->staffBranchA = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
            'branch_id' => $this->branchA->id,
        ]);
        $this->billingBranchA = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
            'branch_id' => $this->branchA->id,
        ]);
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->adminBranchA = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'branch_id' => $this->branchA->id,
        ]);
    }

    // ---------- Applicant create ----------

    #[Test]
    public function branch_locked_staff_applicant_create_hides_branch_dropdown_and_auto_sets_own_branch(): void
    {
        $agentOwn = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $this->branchA->id, 'status' => 'active', 'name' => 'Agent Own Branch']);
        Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $this->branchB->id, 'status' => 'active', 'name' => 'Agent Other Branch']);

        $html = $this->actingAs($this->staffBranchA)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();

        // No branch dropdown rendered.
        $this->assertStringNotContainsString('<select name="branch_id"', $html);

        // Branch is auto-set via a hidden input carrying the user's own branch.
        $this->assertStringContainsString(
            sprintf('name="branch_id" id="branch-select" value="%d"', $this->branchA->id),
            $html
        );
        $this->assertStringContainsString($this->branchA->name, $html);
        $this->assertStringNotContainsString($this->branchB->name, $html);

        // Agents are scoped to the user's own branch only.
        $this->assertStringContainsString('Agent Own Branch', $html);
        $this->assertStringNotContainsString('Agent Other Branch', $html);
    }

    #[Test]
    public function main_office_admin_still_sees_full_branch_dropdown_on_applicant_create(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('<select name="branch_id"', $html);
        $this->assertStringContainsString($this->branchA->name, $html);
        $this->assertStringContainsString($this->branchB->name, $html);
    }

    #[Test]
    public function admin_with_branch_is_locked_on_applicant_create(): void
    {
        $html = $this->actingAs($this->adminBranchA)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();

        // Admin + branch = branch account: no dropdown, branch auto-set.
        $this->assertStringNotContainsString('<select name="branch_id"', $html);
        $this->assertStringContainsString(
            sprintf('name="branch_id" id="branch-select" value="%d"', $this->branchA->id),
            $html
        );
        $this->assertStringContainsString($this->branchA->name, $html);
        $this->assertStringNotContainsString($this->branchB->name, $html);
    }

    #[Test]
    public function admin_with_branch_store_with_other_branch_is_rejected(): void
    {
        $this->actingAs($this->adminBranchA)
            ->post(route('applicants.store'), [
                'first_name' => 'Juan',
                'last_name'  => 'Cruz',
                'source'     => 'Branch',
                'branch_id'  => $this->branchB->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('applicants', 0);
    }

    // ---------- Applicant edit ----------

    #[Test]
    public function branch_locked_staff_applicant_edit_hides_branch_dropdown_for_own_branch_applicant(): void
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $this->branchA->id, 'status' => 'active']);
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branchA->id,
            'agent_id'  => $agent->id,
            'source'    => 'Branch',
        ]);

        $html = $this->actingAs($this->staffBranchA)
            ->get(route('applicants.edit', $applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('<select name="branch_id"', $html);
        $this->assertStringContainsString(
            sprintf('name="branch_id" id="branch-select" value="%d"', $this->branchA->id),
            $html
        );
    }

    // ---------- Applicant store / update server-side enforcement ----------

    #[Test]
    public function branch_locked_staff_store_without_branch_defaults_to_own_branch(): void
    {
        $this->actingAs($this->staffBranchA)
            ->post(route('applicants.store'), [
                'first_name' => 'Juan',
                'last_name'  => 'Cruz',
                'source'     => 'Branch',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('applicants', [
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branchA->id,
        ]);
    }

    #[Test]
    public function branch_locked_staff_store_with_other_branch_is_rejected(): void
    {
        $this->actingAs($this->staffBranchA)
            ->post(route('applicants.store'), [
                'first_name' => 'Juan',
                'last_name'  => 'Cruz',
                'source'     => 'Branch',
                'branch_id'  => $this->branchB->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('applicants', 0);
    }

    // ---------- Expense Request create ----------

    #[Test]
    public function branch_locked_billing_expense_create_hides_branch_dropdown_and_shows_own_branch_only(): void
    {
        $agentOwn = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $this->branchA->id, 'status' => 'active', 'name' => 'Agent Own Branch']);
        Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $this->branchB->id, 'status' => 'active', 'name' => 'Agent Other Branch']);

        $html = $this->actingAs($this->billingBranchA)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('<select name="branch_id"', $html);
        $this->assertStringContainsString($this->branchA->name, $html);
        $this->assertStringNotContainsString($this->branchB->name, $html);

        $this->assertStringContainsString('Agent Own Branch', $html);
        $this->assertStringNotContainsString('Agent Other Branch', $html);
    }

    #[Test]
    public function main_office_billing_still_sees_branch_dropdown_on_expense_create(): void
    {
        $mainOfficeBilling = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
            'branch_id' => null,
        ]);

        $html = $this->actingAs($mainOfficeBilling)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('<select name="branch_id"', $html);
        $this->assertStringContainsString($this->branchA->name, $html);
        $this->assertStringContainsString($this->branchB->name, $html);
    }

    // ---------- Expense Request store server-side enforcement ----------

    private function makeExpenseAccounts(): array
    {
        $officeMain = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'name'        => 'Office Expenses',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
        $officeSub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $officeMain->id,
            'name'        => 'Supplies',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);

        return ['main' => $officeMain, 'sub' => $officeSub];
    }

    #[Test]
    public function branch_locked_billing_store_without_branch_creates_request_on_own_branch(): void
    {
        $accounts = $this->makeExpenseAccounts();

        $this->actingAs($this->billingBranchA)
            ->post(route('expense_request.store'), [
                'notes' => 'Branch supplies',
                'lines' => [
                    [
                        'charge'         => 'office',
                        'sub_account_id' => $accounts['sub']->id,
                        'agent_id'       => null,
                        'applicant_id'   => null,
                        'currency'       => 'PHP',
                        'amount'         => 500.00,
                        'particular'     => 'Paper reams',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('expense_requests', [
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branchA->id,
        ]);
    }

    #[Test]
    public function branch_locked_billing_store_with_other_branch_is_rejected(): void
    {
        $accounts = $this->makeExpenseAccounts();

        $this->actingAs($this->billingBranchA)
            ->post(route('expense_request.store'), [
                'notes'     => 'Wrong branch attempt',
                'branch_id' => $this->branchB->id,
                'lines'     => [
                    [
                        'charge'         => 'office',
                        'sub_account_id' => $accounts['sub']->id,
                        'agent_id'       => null,
                        'applicant_id'   => null,
                        'currency'       => 'PHP',
                        'amount'         => 500.00,
                        'particular'     => 'Paper reams',
                    ],
                ],
            ])
            ->assertSessionHasErrors('branch_id');

        $this->assertDatabaseCount('expense_requests', 0);
    }

    #[Test]
    public function main_office_admin_can_store_expense_for_any_branch(): void
    {
        $accounts = $this->makeExpenseAccounts();

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), [
                'notes'     => 'Main office filing',
                'branch_id' => $this->branchB->id,
                'lines'     => [
                    [
                        'charge'         => 'office',
                        'sub_account_id' => $accounts['sub']->id,
                        'agent_id'       => null,
                        'applicant_id'   => null,
                        'currency'       => 'PHP',
                        'amount'         => 500.00,
                        'particular'     => 'Paper reams',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('expense_requests', [
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branchB->id,
        ]);
    }
}
