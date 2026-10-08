<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\User;
use App\Support\ModuleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "User Access Level 'Encoder'" (2026-10-08).
 *
 * New access level "Encoder" (user_type = encoder) that can ONLY reach the
 * Dashboard + Applicant module. Everything else (FRA, Reports, Receivable,
 * Expense & Payments, Agents Report, Users, Settings, Reference Data, ...)
 * is 403 and hidden from the sidebar.
 */
class UserEncoderAccessLevelTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    private function admin(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ], $attrs));
    }

    private function encoder(): User
    {
        return User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'encoder',
        ]);
    }

    // ---------- Access Level dropdown / labels ----------

    #[Test]
    public function encoder_is_offered_on_the_create_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('<option value="encoder"', false)
            ->assertSee('Encoder', false);
    }

    #[Test]
    public function encoder_is_offered_on_the_agency_scoped_create_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('agencies.users.create', $this->agency))
            ->assertOk()
            ->assertSee('<option value="encoder"', false);
    }

    #[Test]
    public function encoder_label_maps_to_encoder(): void
    {
        $this->assertSame('Encoder', User::accessLabel('encoder'));
        $this->assertArrayHasKey('encoder', User::ACCESS_PRESETS);
    }

    #[Test]
    public function admin_can_create_an_encoder_user(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), [
                'name'                  => 'Encode One',
                'email'                 => 'encoder@example.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'user_type'             => 'encoder',
                'status'                => 'active',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email'     => 'encoder@example.com',
            'user_type' => 'encoder',
        ]);
    }

    // ---------- Module access map ----------

    #[Test]
    public function encoder_tier_allows_only_dashboard_and_applicants(): void
    {
        $this->assertTrue(ModuleAccess::allows('encoder', 'dashboard'));
        $this->assertTrue(ModuleAccess::allows('encoder', 'applicants'));

        foreach ([
            'employers', 'reports', 'receivable', 'expense_request',
            'agent_report', 'users', 'settings', 'custom-fields',
            'branches', 'countries', 'positions', 'status-codes',
            'languages', 'skills', 'report-templates', 'agents',
            'company-profile', 'accounting',
        ] as $module) {
            $this->assertFalse(
                ModuleAccess::allows('encoder', $module),
                "Encoder must NOT access the '{$module}' module."
            );
        }
    }

    #[Test]
    public function encoder_is_not_treated_as_rest_of_account(): void
    {
        $this->assertFalse(ModuleAccess::isRestOfAccount('encoder'));
        $this->assertTrue(ModuleAccess::isRestOfAccount('recruiter'));
    }

    // ---------- Route enforcement (middleware) ----------

    #[Test]
    public function encoder_can_reach_dashboard_and_applicants(): void
    {
        $encoder = $this->encoder();

        $this->actingAs($encoder)->get(route('agency.dashboard'))->assertOk();
        $this->actingAs($encoder)->get(route('applicants.index'))->assertOk();
    }

    #[Test]
    public function encoder_is_forbidden_from_every_other_module(): void
    {
        $encoder = $this->encoder();

        foreach ([
            'employers.index', 'reports.index', 'receivable.index',
            'expense_request.index', 'agent_report.index', 'users.index',
            'settings.index', 'accounts.index', 'branches.index',
            'custom-fields.index', 'agents.index', 'company-profile.show',
        ] as $routeName) {
            $this->actingAs($encoder)
                ->get(route($routeName))
                ->assertForbidden();
        }
    }

    // ---------- Sidebar contract ----------

    #[Test]
    public function encoder_sidebar_shows_only_dashboard_and_applicants(): void
    {
        $html = $this->actingAs($this->encoder())
            ->get(route('agency.dashboard'))
            ->assertOk()
            ->getContent();

        // The <nav> block only (body quick-links are a separate surface).
        preg_match('/<nav\b[^>]*>(.*?)<\/nav>/s', $html, $m);
        $nav = $m[1] ?? $html;

        $this->assertStringContainsString(route('applicants.index'), $nav);
        $this->assertStringContainsString(route('agency.dashboard'), $nav);

        foreach ([
            route('receivable.index'), route('expense_request.index'),
            route('agent_report.index'), route('employers.index'),
            route('reports.index'), route('users.index'),
            route('settings.index'), route('accounts.index'),
            route('branches.index'), route('custom-fields.index'),
            route('agents.index'), route('company-profile.show'),
        ] as $href) {
            $this->assertStringNotContainsString($href, $nav,
                "Encoder sidebar must not expose {$href}.");
        }
    }
}
