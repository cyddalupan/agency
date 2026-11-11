<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\User;
use App\Support\ModuleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6af3ddf8 — "FINAS & CSDI: AGENT REPORT, RECEIVABLE and EXPENSE AND
 * PAYMENT MODULE".
 *
 * "Show the AGENT REPORT, RECEIVABLE and EXPENSE AND PAYMENT MODULEs to Admin,
 * Accounting and Branch User Access" on CSDI and FINAS.
 */
class FinasCsdiFinanceModulesTest extends TestCase
{
    use RefreshDatabase;

    private const FINANCE_MODULES = ['agent_report', 'receivable', 'expense_request'];

    private function agency(string $subdomain): Agency
    {
        return Agency::factory()->create(['name' => strtoupper($subdomain), 'subdomain' => $subdomain]);
    }

    private function user(Agency $agency, string $userType): User
    {
        return User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => $userType,
        ]);
    }

    #[Test]
    public function csdi_grants_all_three_modules_to_admin_accounting_and_branch(): void
    {
        $csdi = $this->agency('csdi');

        foreach (['super_admin', 'admin', 'billing', 'branch'] as $userType) {
            foreach (self::FINANCE_MODULES as $module) {
                $this->assertTrue(
                    ModuleAccess::allows($userType, $module, $csdi->id),
                    "CSDI {$userType} MUST access '{$module}'."
                );
            }
        }
    }

    #[Test]
    public function finas_grants_all_three_modules_to_admin_accounting_and_branch(): void
    {
        $finas = $this->agency('finas');

        foreach (['super_admin', 'admin', 'billing', 'branch'] as $userType) {
            foreach (self::FINANCE_MODULES as $module) {
                $this->assertTrue(
                    ModuleAccess::allows($userType, $module, $finas->id),
                    "FINAS {$userType} MUST access '{$module}'."
                );
            }
        }
    }

    #[Test]
    public function branch_users_on_csdi_and_finas_can_open_the_pages(): void
    {
        foreach (['csdi', 'finas'] as $subdomain) {
            $agency = $this->agency($subdomain);
            $branchUser = $this->user($agency, 'branch');

            $this->actingAs($branchUser)
                ->get(route('receivable.index'))
                ->assertOk();

            $this->actingAs($branchUser)
                ->get(route('expense_request.index'))
                ->assertOk();

            $this->actingAs($branchUser)
                ->get(route('agent_report.index'))
                ->assertOk();
        }
    }

    #[Test]
    public function other_agencies_still_do_not_get_the_agent_report(): void
    {
        $other = $this->agency('omnis');

        foreach (['super_admin', 'admin', 'billing', 'branch'] as $userType) {
            $this->assertFalse(
                ModuleAccess::allows($userType, 'agent_report', $other->id),
                "Agency outside the allow-list must NOT get Agent Report for {$userType}."
            );
        }
    }

    #[Test]
    public function gulf_branch_users_keep_their_narrower_access(): void
    {
        // Gulf is deliberately not in BRANCH_FINANCE_AGENCIES — widening FINAS
        // and CSDI must not quietly widen Gulf too.
        $gulf = $this->agency('gulf');

        $this->assertTrue(ModuleAccess::allows('branch', 'agent_report', $gulf->id));
        $this->assertFalse(ModuleAccess::allows('branch', 'receivable', $gulf->id));
        $this->assertFalse(ModuleAccess::allows('branch', 'expense_request', $gulf->id));
    }

    #[Test]
    public function encoder_and_recruitment_tiers_are_unaffected(): void
    {
        $finas = $this->agency('finas');

        foreach (['encoder', 'recruitment', 'welfare'] as $userType) {
            foreach (self::FINANCE_MODULES as $module) {
                $this->assertFalse(
                    ModuleAccess::allows($userType, $module, $finas->id),
                    "{$userType} must NOT access '{$module}' — the card names Admin, Accounting and Branch only."
                );
            }
        }
    }

    #[Test]
    public function admin_and_accounting_on_csdi_can_open_the_pages(): void
    {
        $csdi = $this->agency('csdi');

        foreach (['admin', 'billing'] as $userType) {
            $user = $this->user($csdi, $userType);

            $this->actingAs($user)->get(route('agent_report.index'))->assertOk();
            $this->actingAs($user)->get(route('receivable.index'))->assertOk();
            $this->actingAs($user)->get(route('expense_request.index'))->assertOk();
        }
    }
}
