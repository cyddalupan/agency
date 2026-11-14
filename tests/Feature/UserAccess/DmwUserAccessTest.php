<?php

namespace Tests\Feature\UserAccess;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6af81c30 — 'FINAS, CSDI: Renaming of Agent, New User Access'.
 *
 * "Create a new User Access and name it to DMW User. These should be the only
 * modules on their access: Dashboard, Company Profile, Employee, Applicant,
 * Backout/Cancelled/Repat, Welfare, FRA, Report."
 */
class DmwUserAccessTest extends TestCase
{
    use RefreshDatabase;

    private function agency(string $subdomain): Agency
    {
        return Agency::factory()->create(['subdomain' => $subdomain, 'name' => ucfirst($subdomain), 'status' => 'active']);
    }

    private function dmwUser(Agency $agency): User
    {
        return User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'dmw_user',
            'status'    => 'active',
        ]);
    }

    #[Test]
    public function dmw_user_can_reach_every_module_on_the_card(): void
    {
        foreach (['finas', 'csdi'] as $subdomain) {
            $user = $this->dmwUser($this->agency($subdomain));

            $this->actingAs($user)->get(route('agency.dashboard'))->assertOk();          // Dashboard
            $this->actingAs($user)->get(route('company-profile.show'))->assertOk();      // Company Profile
            $this->actingAs($user)->get(route('employees.index'))->assertOk();           // Employee
            $this->actingAs($user)->get(route('applicants.index'))->assertOk();          // Applicant
            $this->actingAs($user)->get(route('welfare.index'))->assertOk();             // Welfare
            $this->actingAs($user)->get(route('employers.index'))->assertOk();           // FRA
            $this->actingAs($user)->get(route('reports.index'))->assertOk();             // Report
        }
    }

    #[Test]
    public function dmw_user_is_refused_every_module_not_on_the_card(): void
    {
        $user = $this->dmwUser($this->agency('finas'));

        // Accounting, receipts, users, settings, reference data and the
        // Agent (Marketing) Report are all outside the card's list.
        foreach ([
            'receivable.index',
            'expense_request.index',
            'agent_report.index',
            'general_ledger.index',
            'users.index',
            'settings.index',
            'accounts.index',
            'branches.index',
            'positions.index',
            'languages.index',
            'skills.index',
        ] as $route) {
            $this->actingAs($user)->get(route($route))
                ->assertForbidden("DMW User must not reach {$route}");
        }
    }

    #[Test]
    public function dmw_user_does_not_exist_on_other_tenants(): void
    {
        foreach (['gulf', 'omnis'] as $subdomain) {
            $user = $this->dmwUser($this->agency($subdomain));

            $this->actingAs($user)->get(route('applicants.index'))->assertForbidden();
            $this->actingAs($user)->get(route('welfare.index'))->assertForbidden();
        }
    }

    #[Test]
    public function the_access_level_is_offered_in_the_user_form_only_on_finas_and_csdi(): void
    {
        $this->assertArrayHasKey('dmw_user', User::ACCESS_PRESETS);
        $this->assertSame('DMW User', User::ACCESS_PRESETS['dmw_user']);

        // Super admin viewing a FINAS agency sees the option.
        $super = User::factory()->create(['agency_id' => null, 'user_type' => 'super_admin']);
        $finas = $this->agency('finas');

        $html = $this->actingAs($super)->get(route('agencies.users.create', $finas))->assertOk()->getContent();
        $this->assertStringContainsString('DMW User', $html);

        $gulf = $this->agency('gulf');
        $html = $this->actingAs($super)->get(route('agencies.users.create', $gulf))->assertOk()->getContent();
        $this->assertStringNotContainsString('DMW User', $html);
    }
}
