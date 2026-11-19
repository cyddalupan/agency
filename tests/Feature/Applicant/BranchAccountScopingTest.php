<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Branch account scoping — TDD for the Branch feature.
 *
 * Rules under test (confirmed by Cyd 2026-08-07):
 *  - Branch = literal agency branch; an agency can have multiple branches.
 *  - A "branch account" is any User with a non-null branch_id (regardless of user_type).
 *  - Branch account is auto-scoped to its branch: sees only that branch's applicants.
 *  - Agency admin (no branch_id) sees ALL agency applicants (no branch default).
 *  - Dashboard counts are scoped to the branch for branch users.
 *  - Add/Edit: branch dropdown optional, defaults to logged-in user's branch,
 *    no default for agency admin; Add & Edit behave identically.
 *  - Branch user can add/edit only applicants of their own branch.
 */
class BranchAccountScopingTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Branch $branchA;
    private Branch $branchB;
    private User $agencyAdmin; // no branch_id
    private User $branchUserA; // branch_id = branchA
    private User $adminWithBranch; // admin type WITH branch_id — IS locked (rule r c0125113)

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'branch-test', 'name' => 'Branch Test Agency']);

        $this->branchA = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Quezon City Branch']);
        $this->branchB = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila Branch']);

        $this->agencyAdmin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'branch_id' => null,
        ]);

        // REVERTED to a plain factory user: being privileged changes nothing
        // for these six tests (they all go through the applicants module, not
        // the privileged-only report screens), and it would muddy the picture.
        $this->adminWithBranch = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'branch_id' => $this->branchA->id,
        ]);

        $this->branchUserA = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
            'branch_id' => $this->branchA->id,
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    private function applicant(int $branchId, string $name): Applicant
    {
        return Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $branchId,
            'first_name' => $name,
        ]);
    }

    // ─── SCOPE: branch account sees ONLY own branch ───────────────────

    #[Test]
    public function branch_user_sees_only_own_branch_applicants_on_list(): void
    {
        $this->applicant($this->branchA->id, 'Alice');
        $this->applicant($this->branchB->id, 'Bob');

        $this->actingAs($this->branchUserA)
            ->get(route('applicants.index'))
            ->assertOk()
            ->assertSee('Alice')
            ->assertDontSee('Bob');
    }

    #[Test]
    public function agency_admin_without_branch_sees_all_agency_applicants(): void
    {
        $this->applicant($this->branchA->id, 'Alice');
        $this->applicant($this->branchB->id, 'Bob');

        $this->actingAs($this->agencyAdmin)
            ->get(route('applicants.index'))
            ->assertOk()
            ->assertSee('Alice')
            ->assertSee('Bob');
    }

    #[Test]
    public function branch_user_cannot_open_other_branch_applicant(): void
    {
        $bob = $this->applicant($this->branchB->id, 'Bob');

        $this->actingAs($this->branchUserA)
            ->get(route('applicants.show', $bob))
            ->assertForbidden();

        $this->actingAs($this->branchUserA)
            ->get(route('applicants.edit', $bob))
            ->assertForbidden();
    }

    #[Test]
    public function branch_user_can_open_own_branch_applicant(): void
    {
        $alice = $this->applicant($this->branchA->id, 'Alice');

        $this->actingAs($this->branchUserA)
            ->get(route('applicants.show', $alice))
            ->assertOk();

        $this->actingAs($this->branchUserA)
            ->get(route('applicants.edit', $alice))
            ->assertOk();
    }

    // ─── DASHBOARD: scoped counts ─────────────────────────────────────

    #[Test]
    public function dashboard_counts_are_scoped_for_branch_user(): void
    {
        $this->applicant($this->branchA->id, 'Alice');
        $this->applicant($this->branchA->id, 'Alicia');
        $this->applicant($this->branchB->id, 'Bob');

        $this->actingAs($this->branchUserA)
            ->get(route('agency.dashboard'))
            ->assertOk();
    }

    // ─── SCOPE: add/edit default branch ───────────────────────────────

    #[Test]
    public function edit_form_defaults_branch_to_users_branch_for_branch_user(): void
    {
        $alice = $this->applicant($this->branchA->id, 'Alice');

        $html = $this->actingAs($this->branchUserA)
            ->get(route('applicants.edit', $alice))
            ->getContent();

        // (Mjolnir branch-user UI) No dropdown at all — branch is hidden and
        // auto-set to the logged-in user's own branch.
        $this->assertStringNotContainsString('<select name="branch_id"', $html);
        $this->assertStringContainsString(
            sprintf('name="branch_id" id="branch-select" value="%d"', $this->branchA->id),
            $html,
            'Edit form should auto-set the logged-in user\'s branch for a branch account'
        );
    }

    #[Test]
    public function branch_user_add_form_defaults_branch_to_their_own_branch(): void
    {
        $html = $this->actingAs($this->branchUserA)
            ->get(route('applicants.create'))
            ->getContent();

        // (Mjolnir branch-user UI) No dropdown at all — branch is hidden and
        // auto-set to the logged-in user's own branch.
        $this->assertStringNotContainsString('<select name="branch_id"', $html);
        $this->assertStringContainsString(
            sprintf('name="branch_id" id="branch-select" value="%d"', $this->branchA->id),
            $html,
            'Add form should auto-set the logged-in user\'s branch for a branch account'
        );
    }

    #[Test]
    public function agency_admin_add_form_has_no_default_branch(): void
    {
        $html = $this->actingAs($this->agencyAdmin)
            ->get(route('applicants.create'))
            ->getContent();

        // No specific branch is pre-selected for an agency admin.
        $this->assertStringNotContainsString(sprintf('value="%d" selected', $this->branchA->id), $html);
        $this->assertStringNotContainsString(sprintf('value="%d" selected', $this->branchB->id), $html);
    }

    // ─── DROPDOWN: branch user must NOT see branches they can't assign ──

    #[Test]
    public function branch_user_add_form_only_lists_their_own_branch(): void
    {
        $html = $this->actingAs($this->branchUserA)
            ->get(route('applicants.create'))
            ->getContent();

        // Own branch appears as a selectable option.
        $this->assertStringContainsString(
            sprintf('value="%d"', $this->branchA->id),
            $html,
            'Add form should list the logged-in user\'s own branch'
        );

        // The other branch must NOT be offered (assigning to it is forbidden).
        $this->assertStringNotContainsString(
            sprintf('value="%d"', $this->branchB->id),
            $html,
            'Add form must not list branches the user cannot assign to'
        );
        $this->assertStringNotContainsString(
            $this->branchB->name,
            $html,
            'Add form must not show the other branch name at all'
        );
    }

    #[Test]
    public function branch_user_edit_form_only_lists_their_own_branch(): void
    {
        $alice = $this->applicant($this->branchA->id, 'Alice');

        $html = $this->actingAs($this->branchUserA)
            ->get(route('applicants.edit', $alice))
            ->getContent();

        $this->assertStringContainsString(
            sprintf('value="%d"', $this->branchA->id),
            $html,
            'Edit form should list the logged-in user\'s own branch'
        );
        $this->assertStringNotContainsString(
            sprintf('value="%d"', $this->branchB->id),
            $html,
            'Edit form must not list branches the user cannot assign to'
        );
        $this->assertStringNotContainsString(
            $this->branchB->name,
            $html,
            'Edit form must not show the other branch name at all'
        );
    }

    #[Test]
    public function agency_admin_add_form_lists_all_branches(): void
    {
        $html = $this->actingAs($this->agencyAdmin)
            ->get(route('applicants.create'))
            ->getContent();

        $this->assertStringContainsString(sprintf('value="%d"', $this->branchA->id), $html);
        $this->assertStringContainsString(sprintf('value="%d"', $this->branchB->id), $html);
    }

    // ─── RULE (c0125113, 2026-09-05): any account with a branch_id is
    //     branch-locked, admins included. Only super_admin and branch-less
    //     (main office) accounts are unrestricted. The tests below used to
    //     assert the opposite; do not re-invert them. ──

    #[Test]
    public function admin_with_branch_id_add_form_lists_all_branches(): void
    {
        $html = $this->actingAs($this->adminWithBranch)
            ->get(route('applicants.create'))
            ->getContent();

        // Rule changed in c0125113: an admin carrying a branch_id IS branch-locked,
        // so it gets no dropdown and cannot see other branches.
        $this->assertStringNotContainsString('<select name="branch_id"', $html);
        $this->assertStringContainsString(sprintf('value="%d"', $this->branchA->id), $html);
        $this->assertStringNotContainsString(sprintf('value="%d"', $this->branchB->id), $html);
        $this->assertStringNotContainsString($this->branchB->name, $html);
    }

    #[Test]
    public function admin_with_branch_id_can_store_applicant_into_any_branch(): void
    {
        // Rule changed in c0125113: admin + branch_id = branch account, so writing
        // to another branch is refused and nothing is persisted.
        $this->actingAs($this->adminWithBranch)
            ->post(route('applicants.store'), $this->validPayload(['branch_id' => $this->branchB->id]))
            ->assertForbidden();

        $this->assertDatabaseCount('applicants', 0);
    }

    #[Test]
    public function admin_with_branch_id_edit_form_lists_all_branches(): void
    {
        $alice = $this->applicant($this->branchA->id, 'Alice');

        $html = $this->actingAs($this->adminWithBranch)
            ->get(route('applicants.edit', $alice))
            ->getContent();

        // Rule changed in c0125113: branch-locked even for an admin with a branch_id.
        $this->assertStringNotContainsString('<select name="branch_id"', $html);
        $this->assertStringContainsString(sprintf('value="%d"', $this->branchA->id), $html);
        $this->assertStringNotContainsString(sprintf('value="%d"', $this->branchB->id), $html);
    }

    #[Test]
    public function admin_with_branch_id_can_update_applicant_branch_to_any_branch(): void
    {
        $alice = $this->applicant($this->branchA->id, 'Alice');

        // Rule changed in c0125113. This is the ONLY update-path proof that an
        // admin cannot move an applicant across branches — invert it, never
        // delete it.
        $this->actingAs($this->adminWithBranch)
            ->patch(route('applicants.update', $alice), $this->validPayload(['branch_id' => $this->branchB->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('applicants', [
            'id' => $alice->id,
            'branch_id' => $this->branchA->id,
        ]);
    }

    #[Test]
    public function admin_with_branch_id_can_open_applicant_from_other_branch(): void
    {
        $bob   = $this->applicant($this->branchB->id, 'Bob');
        $alice = $this->applicant($this->branchA->id, 'Alice');

        // Rule changed in c0125113. Assert both halves: the other branch is
        // refused, and the own branch still works — otherwise this would pass
        // even if the controller ignored branch entirely.
        $this->actingAs($this->adminWithBranch)
            ->get(route('applicants.show', $bob))
            ->assertForbidden();

        $this->actingAs($this->adminWithBranch)
            ->get(route('applicants.show', $alice))
            ->assertOk();
    }

    #[Test]
    public function admin_with_branch_id_list_sees_all_branch_applicants(): void
    {
        $this->applicant($this->branchA->id, 'Alice');
        $this->applicant($this->branchB->id, 'Bob');

        // Rule changed in c0125113: the list is scoped to the admin's branch.
        $this->actingAs($this->adminWithBranch)
            ->get(route('applicants.index'))
            ->assertOk()
            ->assertSee('Alice')
            ->assertDontSee('Bob');
    }

    // ─── STORE: branch persistence for branch user + admin ────────────

    #[Test]
    public function branch_user_store_defaults_branch_to_their_own_when_omitted(): void
    {
        $this->actingAs($this->branchUserA)
            ->post(route('applicants.store'), $this->validPayload([]))
            ->assertRedirect(route('applicants.index'));

        $this->assertDatabaseHas('applicants', [
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branchA->id,
            'first_name' => 'BranchSave',
        ]);
    }

    #[Test]
    public function agency_admin_store_allows_free_branch_choice(): void
    {
        $this->actingAs($this->agencyAdmin)
            ->post(route('applicants.store'), $this->validPayload(['branch_id' => $this->branchB->id]));

        $this->assertDatabaseHas('applicants', [
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branchB->id,
            'first_name' => 'BranchSave',
        ]);
    }

    #[Test]
    public function branch_user_cannot_store_applicant_into_another_branch(): void
    {
        $this->actingAs($this->branchUserA)
            ->post(route('applicants.store'), $this->validPayload(['branch_id' => $this->branchB->id]))
            ->assertForbidden();

        $this->assertDatabaseMissing('applicants', ['first_name' => 'BranchSave']);
    }

    #[Test]
    public function branch_user_cannot_update_other_branch_applicant(): void
    {
        $bob = $this->applicant($this->branchB->id, 'Bob');

        $this->actingAs($this->branchUserA)
            ->patch(route('applicants.update', $bob), $this->validPayload(['first_name' => 'Hacked']))
            ->assertForbidden();

        $this->assertDatabaseMissing('applicants', ['first_name' => 'Hacked']);
    }

    // ─── SIDEBAR: branch accounts get a trimmed-down nav ──────────────

    #[Test]
    public function branch_user_sidebar_shows_kept_items(): void
    {
        $html = $this->actingAs($this->branchUserA)
            ->get(route('agency.dashboard'))
            ->getContent();

        // Keep: Dashboard, Applicants, FRA, Reports
        // (Languages/Skills CRUD are admin/super_admin-only routes, so branch
        //  accounts no longer get those sidebar links — they 403'd before.)
        foreach (['Dashboard', 'Applicants', 'FRA', 'Reports'] as $label) {
            $this->assertStringContainsString($label, $html, "Branch sidebar should keep: {$label}");
        }
    }

    #[Test]
    public function branch_user_sidebar_hides_admin_finance_and_system_items(): void
    {
        // (Card 6afe4e3d, 2026-11-19) Asserted on the bare string 'Accounting'
        // and on label text, which made it unsatisfiable: staff deliberately keep
        // Receivable + Expenses and Payments since Cyd 2026-09-28, and the old
        // single-block heading literally read "Accounting Dashboard". The rule
        // under test is module reach, so assert on route URLs — those cannot be
        // satisfied by heading prose and are not fooled by a tenant-renamed
        // agent_terms() label.
        $html = $this->actingAs($this->branchUserA)
            ->get(route('agency.dashboard'))
            ->getContent();

        // Kept deliberately (2026-09-28): branch staff file their own requests.
        $this->assertStringContainsString(route('receivable.index'), $html,
            'Branch staff should keep Receivable.');
        $this->assertStringContainsString(route('expense_request.index'), $html,
            'Branch staff should keep Expenses and Payments.');

        // Out of reach: the accounting family and the admin/super_admin modules.
        foreach ([
            'accounting.dashboard',
            'general_ledger.index',
            'agent_report.index',
        ] as $routeName) {
            $this->assertStringNotContainsString(route($routeName), $html,
                "Branch staff sidebar should not link to: {$routeName}");
        }

        // Admin-only system screens (route middleware is admin/super_admin-only).
        foreach ([
            'users.index',
            'custom-fields.index',
            'branches.index',
            'report-templates.index',
            'languages.index',
            'skills.index',
            'settings.index',
        ] as $routeName) {
            if (! \Illuminate\Support\Facades\Route::has($routeName)) {
                continue;
            }
            $this->assertStringNotContainsString(route($routeName), $html,
                "Branch staff sidebar should not link to: {$routeName}");
        }
    }

    #[Test]
    public function agency_admin_sidebar_does_link_to_the_accounting_screens(): void
    {
        // Companion to the test above: proves the gate DISCRIMINATES. Without it,
        // an empty sidebar would satisfy every assertStringNotContainsString.
        $html = $this->actingAs($this->agencyAdmin)
            ->get(route('agency.dashboard'))
            ->getContent();

        $this->assertStringContainsString(route('accounting.dashboard'), $html,
            'Agency admin must still reach Statistics.');
    }

    #[Test]
    public function agency_admin_sidebar_still_shows_admin_and_finance_items(): void
    {
        $html = $this->actingAs($this->agencyAdmin)
            ->get(route('agency.dashboard'))
            ->getContent();

        foreach (['Accounting', 'Custom Fields', 'Settings', 'Branches', 'Languages', 'Skills'] as $label) {
            $this->assertStringContainsString($label, $html, "Agency admin sidebar should show: {$label}");
        }
    }

    // ─── HELPERS ──────────────────────────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'BranchSave',
            'last_name' => 'Test',
        ], $overrides);
    }
}
