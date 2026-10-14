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
 * Branch-restrictions kill-switch — TDD for the TEMPORARY lift
 * (Mjolnir/Cyd 2026-10-14).
 *
 * With config('mjolnir.branch_restrictions') = false a branch account is no
 * longer locked to its own branch: it sees every agency applicant, may open /
 * edit other branches' records, and the model reports "not branch locked".
 * Module access per account type is intentionally unchanged.
 */
class BranchRestrictionsDisabledTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Branch $branchA;
    private Branch $branchB;
    private User $branchUserA;

    protected function setUp(): void
    {
        parent::setUp();

        // The kill-switch is OFF for this whole suite.
        config(['mjolnir.branch_restrictions' => false]);

        $this->agency = Agency::factory()->create(['subdomain' => 'branch-lift', 'name' => 'Branch Lift Agency']);
        $this->branchA = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Quezon City Branch']);
        $this->branchB = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila Branch']);

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

    #[Test]
    public function branch_user_is_not_reported_as_locked(): void
    {
        $this->assertFalse($this->branchUserA->isBranchLocked());
        $this->assertFalse($this->branchUserA->isBranchAccount());
    }

    #[Test]
    public function branch_user_sees_every_branch_applicant_on_list(): void
    {
        $this->applicant($this->branchA->id, 'Alice');
        $this->applicant($this->branchB->id, 'Bob');

        $this->actingAs($this->branchUserA)
            ->get(route('applicants.index'))
            ->assertOk()
            ->assertSee('Alice')
            ->assertSee('Bob');
    }

    #[Test]
    public function branch_user_can_open_and_edit_other_branch_applicant(): void
    {
        $bob = $this->applicant($this->branchB->id, 'Bob');

        $this->actingAs($this->branchUserA)
            ->get(route('applicants.show', $bob))
            ->assertOk();

        $this->actingAs($this->branchUserA)
            ->get(route('applicants.edit', $bob))
            ->assertOk();
    }

    #[Test]
    public function module_access_is_unchanged_while_lifted(): void
    {
        // Lifting branch scoping must NOT silently grant new modules.
        $this->assertTrue($this->branchUserA->canAccessModule('applicants'));
        $this->assertFalse($this->branchUserA->canAccessModule('users'));
        $this->assertFalse($this->branchUserA->canAccessModule('settings'));
    }
}
