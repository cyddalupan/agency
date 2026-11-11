<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\User;
use App\Support\ModuleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "GULF: Agent Reports module" (Cyd 2026-11-09):
 *
 *   "Show Agents Report Module to Admin, Accounting, and other branch user
 *    access only (Bacolod, Cdo, Davao, Iloilo, Pangasinan)" — Gulf Only.
 *
 * So: Admin, Accounting (billing) and Branch keep it on the Gulf tenant;
 * staff / encoder / recruitment / welfare lose it, and every other agency
 * loses it entirely (including that agency's own admin).
 */
class AgentReportAccessScopeTest extends TestCase
{
    use RefreshDatabase;

    private Agency $gulf;
    private Agency $other;
    private Branch $gulfBranch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gulf = Agency::factory()->create(['subdomain' => 'gulf']);
        // (Card 6af3ddf8, 2026-11-11) CSDI and FINAS were added to the
        // Agents Report allow-list, so this fixture must use a tenant that is
        // genuinely NOT named there — otherwise "other agency" would test an
        // agency that is in fact allowed.
        $this->other = Agency::factory()->create(['subdomain' => 'omnis']);
        $this->gulfBranch = Branch::factory()->create([
            'agency_id' => $this->gulf->id,
            'name'      => 'BACOLOD',
        ]);

        Agent::factory()->create([
            'agency_id' => $this->gulf->id,
            'branch_id' => $this->gulfBranch->id,
            'name'      => 'BACOLOD AGENT',
        ]);
    }

    private function user(Agency $agency, string $type, ?Branch $branch = null, string $name = 'QA USER'): User
    {
        return User::factory()->create([
            'agency_id' => $agency->id,
            'branch_id' => $branch?->id,
            'user_type' => $type,
            'name'      => $name,
        ]);
    }

    #[Test]
    public function gulf_admin_and_accounting_keep_the_module(): void
    {
        $this->assertTrue(ModuleAccess::allows('admin', 'agent_report', $this->gulf->id));
        $this->assertTrue(ModuleAccess::allows('billing', 'agent_report', $this->gulf->id));

        $this->actingAs($this->user($this->gulf, 'billing'))
            ->get(route('agent_report.index'))
            ->assertOk();
    }

    #[Test]
    public function gulf_branch_users_keep_the_module(): void
    {
        $branchUser = $this->user($this->gulf, 'branch', $this->gulfBranch, 'BACOLOD BRANCH');

        $this->assertTrue($branchUser->canAccessModule('agent_report'));

        $this->actingAs($branchUser)
            ->get(route('agent_report.index'))
            ->assertOk()
            ->assertSee('BACOLOD AGENT');
    }

    /** "Rest of Account" (staff) was previously allowed — the card removes it. */
    #[Test]
    public function gulf_staff_users_lose_the_module(): void
    {
        foreach (['staff', 'operation', 'processor', 'paralegal'] as $type) {
            $this->assertFalse(
                ModuleAccess::allows($type, 'agent_report', $this->gulf->id),
                "{$type} must not reach Agents Report"
            );
        }

        $staff = $this->user($this->gulf, 'staff', $this->gulfBranch, 'GULF STAFF');

        $this->actingAs($staff)
            ->get(route('agent_report.index'))
            ->assertForbidden();

        // ...and the sidebar entry is gone.
        $this->actingAs($staff)
            ->get(route('applicants.index'))
            ->assertOk()
            ->assertDontSee('Agents Report');
    }

    #[Test]
    public function other_agencies_never_get_the_module(): void
    {
        foreach (['admin', 'billing', 'branch', 'staff'] as $type) {
            $this->assertFalse(
                ModuleAccess::allows($type, 'agent_report', $this->other->id),
                "{$type} on a tenant outside the allow-list must not reach Agents Report"
            );
        }

        $this->actingAs($this->user($this->other, 'admin', null, 'CSDI ADMIN'))
            ->get(route('agent_report.index'))
            ->assertForbidden();
    }

    /** The Gulf gate must not leak into unrelated modules. */
    #[Test]
    public function gulf_gate_does_not_affect_other_modules(): void
    {
        foreach (['dashboard', 'applicants', 'receivable', 'expense_request', 'reports'] as $module) {
            $this->assertTrue(
                ModuleAccess::allows('staff', $module, $this->gulf->id),
                "staff must keep {$module}"
            );
        }

        $this->assertTrue(ModuleAccess::allows('staff', 'applicants', $this->other->id));
        $this->assertTrue(ModuleAccess::allows('admin', 'agent_report', $this->gulf->id));
    }
}
