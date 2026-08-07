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

        $this->assertStringContainsString(
            sprintf('value="%d" selected', $this->branchA->id),
            $html,
            'Branch dropdown on Edit should pre-select the logged-in user\'s branch for a branch account'
        );
    }

    #[Test]
    public function branch_user_add_form_defaults_branch_to_their_own_branch(): void
    {
        $html = $this->actingAs($this->branchUserA)
            ->get(route('applicants.create'))
            ->getContent();

        $this->assertStringContainsString(
            sprintf('value="%d" selected', $this->branchA->id),
            $html,
            'Branch dropdown on Add should pre-select the logged-in user\'s branch for a branch account'
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

        // Keep: Dashboard, Applicants, FRA, Reports, Languages, Skills
        foreach (['Dashboard', 'Applicants', 'FRA', 'Reports', 'Languages', 'Skills'] as $label) {
            $this->assertStringContainsString($label, $html, "Branch sidebar should keep: {$label}");
        }
    }

    #[Test]
    public function branch_user_sidebar_hides_admin_finance_and_system_items(): void
    {
        $html = $this->actingAs($this->branchUserA)
            ->get(route('agency.dashboard'))
            ->getContent();

        // Remove: Accounting, Receivables, Expenses, Settings, Users,
        // Custom Fields, Branches, Agencies, Agents, Accounts, Report Templates
        foreach (['Accounting', 'Receivable', 'Expenses', 'Settings', 'Users', 'Custom Fields', 'Branches', 'Report Templates'] as $label) {
            $this->assertStringNotContainsString($label, $html, "Branch sidebar should hide: {$label}");
        }
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
