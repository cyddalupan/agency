<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\User;
use App\Support\ModuleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "FINAS: User Access" (2026-10-20).
 *
 * Access roles requested by FINAS:
 *  - Admin        : every module + delete + status revert (unchanged).
 *  - Accounting   : Applicants, Accounting/Statistics and the Accounts module.
 *  - Recruitment  : Applicants, FRA, Reference Data, Lookups. Case / Welfare /
 *                   Accounting are view-only (no add transaction).
 *  - Encoder      : separate link, Add Applicant only.
 *  - Welfare      : Applicants with Case & Welfare tabs.
 *  - Branch       : same as Recruitment minus Reference Data & Lookups.
 */
class UserFinasAccessRolesTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    private function user(string $type, array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'agency_id' => $this->agency->id,
            'user_type' => $type,
        ], $attrs));
    }

    private function admin(): User
    {
        return $this->user('admin');
    }

    // ---------- Access-level dropdown / labels ----------

    #[Test]
    public function recruitment_and_welfare_are_offered_on_the_create_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('<option value="recruitment"', false)
            ->assertSee('<option value="welfare"', false)
            ->assertSee('Recruitment', false)
            ->assertSee('Welfare', false);
    }

    #[Test]
    public function access_labels_map_to_the_finas_roles(): void
    {
        $this->assertSame('Recruitment', User::accessLabel('recruitment'));
        $this->assertSame('Welfare', User::accessLabel('welfare'));
        $this->assertArrayHasKey('recruitment', User::ACCESS_PRESETS);
        $this->assertArrayHasKey('welfare', User::ACCESS_PRESETS);
    }

    #[Test]
    public function admin_can_create_recruitment_and_welfare_users(): void
    {
        foreach (['recruitment', 'welfare'] as $role) {
            $this->actingAs($this->admin())
                ->post(route('users.store'), [
                    'name'                  => ucfirst($role).' User',
                    'email'                 => $role.'@example.com',
                    'password'              => 'password123',
                    'password_confirmation' => 'password123',
                    'user_type'             => $role,
                    'status'                => 'active',
                ])
                ->assertRedirect(route('users.index'));

            $this->assertDatabaseHas('users', [
                'email'     => $role.'@example.com',
                'user_type' => $role,
            ]);
        }
    }

    // ---------- Module map ----------

    #[Test]
    public function recruitment_gets_applicants_fra_reference_data_and_lookups(): void
    {
        foreach ([
            'dashboard', 'applicants', 'employers', 'branches', 'countries',
            'positions', 'status-codes', 'languages', 'skills',
        ] as $module) {
            $this->assertTrue(
                ModuleAccess::allows('recruitment', $module),
                "Recruitment MUST access '{$module}'."
            );
        }

        foreach ([
            'users', 'settings', 'accounts', 'reports', 'receivable',
            'expense_request', 'agent_report', 'custom-fields', 'agents',
            'accounting', 'report-templates',
        ] as $module) {
            $this->assertFalse(
                ModuleAccess::allows('recruitment', $module),
                "Recruitment must NOT access '{$module}'."
            );
        }
    }

    #[Test]
    public function branch_gets_recruitment_minus_reference_data_and_lookups(): void
    {
        $this->assertTrue(ModuleAccess::allows('branch', 'applicants'));
        $this->assertTrue(ModuleAccess::allows('branch', 'employers'));

        foreach ([
            'branches', 'countries', 'positions', 'status-codes',
            'languages', 'skills',
        ] as $module) {
            $this->assertFalse(
                ModuleAccess::allows('branch', $module),
                "Branch must NOT access '{$module}'."
            );
        }
    }

    #[Test]
    public function welfare_gets_applicants_only(): void
    {
        $this->assertTrue(ModuleAccess::allows('welfare', 'dashboard'));
        $this->assertTrue(ModuleAccess::allows('welfare', 'applicants'));

        foreach ([
            'employers', 'branches', 'languages', 'skills', 'reports',
            'receivable', 'expense_request', 'agent_report', 'users',
            'settings', 'accounts', 'accounting',
        ] as $module) {
            $this->assertFalse(
                ModuleAccess::allows('welfare', $module),
                "Welfare must NOT access '{$module}'."
            );
        }
    }

    #[Test]
    public function accounting_keeps_accounts_module_but_not_settings(): void
    {
        $this->assertTrue(ModuleAccess::allows('billing', 'accounts'));
        $this->assertTrue(ModuleAccess::allows('billing', 'applicants'));
        $this->assertFalse(ModuleAccess::allows('billing', 'settings'));
        $this->assertFalse(ModuleAccess::allows('billing', 'users'));
    }

    #[Test]
    public function finas_accounting_role_only_gets_applicants_accounting_and_accounts(): void
    {
        $finas = Agency::factory()->create(['name' => 'Finas Manpower', 'subdomain' => 'finas']);
        $accounting = User::factory()->create([
            'agency_id' => $finas->id,
            'user_type' => 'billing',
        ]);

        foreach (['applicants', 'accounting', 'accounts'] as $module) {
            $this->assertTrue($accounting->canAccessModule($module), "FINAS Accounting MUST access '{$module}'.");
        }

        foreach (['employers', 'reports', 'receivable', 'expense_request', 'agent_report', 'settings', 'users'] as $module) {
            $this->assertFalse($accounting->canAccessModule($module), "FINAS Accounting must NOT access '{$module}'.");
        }

        $this->actingAs($accounting)->get(route('accounts.index'))->assertOk();
        $this->actingAs($accounting)->get(route('applicants.index'))->assertOk();
        $this->actingAs($accounting)->get(route('employers.index'))->assertForbidden();
    }

    #[Test]
    public function non_finas_accounting_keeps_the_existing_broader_access(): void
    {
        $billing = $this->user('billing');

        $this->assertTrue($billing->canAccessModule('employers'));
        $this->assertTrue($billing->canAccessModule('reports'));
        $this->assertTrue($billing->canAccessModule('receivable'));
    }

    #[Test]
    public function finas_roles_are_not_treated_as_rest_of_account(): void
    {
        $this->assertFalse(ModuleAccess::isRestOfAccount('recruitment'));
        $this->assertFalse(ModuleAccess::isRestOfAccount('branch'));
        $this->assertFalse(ModuleAccess::isRestOfAccount('welfare'));
        $this->assertTrue(ModuleAccess::isRestOfAccount('staff'));
    }

    // ---------- Route enforcement ----------

    #[Test]
    public function recruitment_can_open_its_modules_but_not_accounting(): void
    {
        $recruitment = $this->user('recruitment');

        $this->actingAs($recruitment)->get(route('applicants.index'))->assertOk();
        $this->actingAs($recruitment)->get(route('employers.index'))->assertOk();
        $this->actingAs($recruitment)->get(route('branches.index'))->assertOk();

        foreach (['receivable.index', 'expense_request.index', 'users.index', 'settings.index', 'accounts.index'] as $routeName) {
            $this->actingAs($recruitment)->get(route($routeName))->assertForbidden();
        }
    }

    #[Test]
    public function welfare_can_open_applicants_but_not_fra(): void
    {
        $welfare = $this->user('welfare');

        $this->actingAs($welfare)->get(route('applicants.index'))->assertOk();
        $this->actingAs($welfare)->get(route('employers.index'))->assertForbidden();
    }

    #[Test]
    public function branch_can_open_fra_but_not_reference_data(): void
    {
        $branch = $this->user('branch', ['branch_id' => Branch::factory()->create(['agency_id' => $this->agency->id])->id]);

        $this->actingAs($branch)->get(route('employers.index'))->assertOk();
        $this->actingAs($branch)->get(route('branches.index'))->assertForbidden();
        $this->actingAs($branch)->get(route('languages.index'))->assertForbidden();
    }

    #[Test]
    public function encoder_can_only_add_applicants(): void
    {
        $encoder = $this->user('encoder');

        // Dashboard + the Add Applicant form work.
        $this->actingAs($encoder)->get(route('agency.dashboard'))->assertOk();
        $this->actingAs($encoder)->get(route('applicants.create'))->assertOk();

        // The applicant list/show are off-limits ("Add Applicant function only").
        $this->actingAs($encoder)->get(route('applicants.index'))->assertForbidden();

        // Sidebar shows the separate Add Applicant link, not the Applicants list.
        $html = $this->actingAs($encoder)->get(route('agency.dashboard'))->getContent();
        preg_match('/<nav\b[^>]*>(.*?)<\/nav>/s', $html, $m);
        $nav = $m[1] ?? $html;

        $this->assertStringContainsString(route('applicants.create'), $nav);
        $this->assertStringNotContainsString('href="'.route('applicants.index').'"', $nav);
        $this->assertStringContainsString('Add Applicant', $nav);
    }

    #[Test]
    public function case_writes_are_view_only_for_recruitment_and_branch(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => null,
        ]);

        foreach (['recruitment', 'branch'] as $role) {
            $user = $this->user($role);
            $this->actingAs($user)->post(
                route('applicants.sub.store', [$applicant, 'cases']),
                ['title' => 'Should not save']
            )->assertForbidden();
        }

        // Welfare keeps full Case access (validation error = reached the controller).
        $welfare = $this->user('welfare');
        $this->actingAs($welfare)->post(
            route('applicants.sub.store', [$applicant, 'cases']),
            ['title' => 'Welfare case']
        )->assertSessionHasNoErrors()->assertRedirect();
    }

    #[Test]
    public function recruitment_sidebar_hides_accounting_and_accounts(): void
    {
        $html = $this->actingAs($this->user('recruitment'))
            ->get(route('agency.dashboard'))
            ->getContent();

        preg_match('/<nav\b[^>]*>(.*?)<\/nav>/s', $html, $m);
        $nav = $m[1] ?? $html;

        $this->assertStringContainsString(route('applicants.index'), $nav);
        $this->assertStringContainsString(route('employers.index'), $nav);
        $this->assertStringContainsString(route('branches.index'), $nav);

        foreach ([
            route('receivable.index'), route('expense_request.index'),
            route('users.index'), route('settings.index'), route('accounts.index'),
        ] as $href) {
            $this->assertStringNotContainsString($href, $nav);
        }
    }
}
