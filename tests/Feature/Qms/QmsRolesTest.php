<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\ControlledDocument;
use App\Models\User;
use App\Support\ModuleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-09 (card 6afa0da6eb58094f7e27f315) — QMS Manager + Internal Auditor.
 *
 * Acceptance: a user with each role logs in and gets the intended gate on one
 * sample screen; existing roles are unaffected.
 *
 * Spec §19: QMS Manager has full CRUD on QMS modules; Internal Auditor is
 * read-only on audits and may not alter source evidence.
 */
class QmsRolesTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->withQms()->create();
        app()->instance('tenant_agency', $this->agency);
    }

    private function user(string $type): User
    {
        return User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => $type]);
    }

    // ─── role registration ───────────────────────────────────────────

    #[Test]
    public function both_roles_exist_in_the_presets_with_human_labels(): void
    {
        $this->assertArrayHasKey('qms_manager', User::ACCESS_PRESETS);
        $this->assertArrayHasKey('internal_auditor', User::ACCESS_PRESETS);

        $this->assertSame('QMS Manager', User::accessLabel('qms_manager'));
        $this->assertSame('Internal Auditor', User::accessLabel('internal_auditor'));
    }

    #[Test]
    public function adding_the_roles_did_not_disturb_existing_ones(): void
    {
        // The pre-existing presets must still be present and unchanged.
        $expected = [
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            'billing'     => 'Accounting',
            'recruitment' => 'Recruitment',
            'welfare'     => 'Welfare',
            'staff'       => 'Receptionist',
            'processor'   => 'Processing',
            'paralegal'   => 'Paralegal',
            'branch'      => 'Branch',
            'operation'   => 'Operation',
            'encoder'     => 'Encoder',
            'dmw_user'    => 'DMW User',
            'employer'    => 'FRA (Employer)',
        ];

        foreach ($expected as $key => $label) {
            $this->assertArrayHasKey($key, User::ACCESS_PRESETS, "Existing role {$key} disappeared.");
            $this->assertSame($label, User::ACCESS_PRESETS[$key], "Existing role {$key} was relabelled.");
        }
    }

    // ─── QMS Manager: full CRUD ──────────────────────────────────────

    #[Test]
    public function a_qms_manager_can_open_the_register(): void
    {
        $this->actingAs($this->user('qms_manager'))
            ->get(route('controlled_documents.index'))
            ->assertOk();
    }

    #[Test]
    public function a_qms_manager_can_create_a_document(): void
    {
        $this->actingAs($this->user('qms_manager'))
            ->post(route('controlled_documents.store'), [
                'code'  => 'QMS-900',
                'title' => 'Created by QMS Manager',
            ])
            ->assertRedirect(route('controlled_documents.index'));

        $this->assertDatabaseHas('controlled_documents', ['code' => 'QMS-900']);
    }

    #[Test]
    public function a_qms_manager_can_open_the_audit_trail(): void
    {
        $this->actingAs($this->user('qms_manager'))
            ->get(route('audit_trail.index'))
            ->assertOk();
    }

    // ─── Internal Auditor: read-only ─────────────────────────────────

    #[Test]
    public function an_internal_auditor_can_read_the_document_register(): void
    {
        ControlledDocument::withoutGlobalScopes()->create([
            'agency_id'       => $this->agency->id,
            'code'            => 'QMS-001',
            'title'           => 'Audited Doc',
            'status'          => ControlledDocument::STATUS_DRAFT,
            'confidentiality' => 'internal',
        ]);

        $response = $this->actingAs($this->user('internal_auditor'))
            ->get(route('controlled_documents.index'));

        $response->assertOk();
        $response->assertSee('QMS-001');
    }

    #[Test]
    public function an_internal_auditor_can_read_the_audit_trail(): void
    {
        $this->actingAs($this->user('internal_auditor'))
            ->get(route('audit_trail.index'))
            ->assertOk();
    }

    #[Test]
    public function an_internal_auditor_cannot_create_a_document(): void
    {
        $this->actingAs($this->user('internal_auditor'))
            ->get(route('controlled_documents.create'))
            ->assertForbidden();

        $this->actingAs($this->user('internal_auditor'))
            ->post(route('controlled_documents.store'), ['code' => 'X-1', 'title' => 'Nope'])
            ->assertForbidden();

        $this->assertDatabaseMissing('controlled_documents', ['code' => 'X-1']);
    }

    #[Test]
    public function an_internal_auditor_cannot_reach_non_qms_modules(): void
    {
        $auditor = $this->user('internal_auditor');

        foreach (['applicants.index', 'receivable.index', 'settings.index'] as $routeName) {
            if (! \Illuminate\Support\Facades\Route::has($routeName)) {
                continue;
            }

            $this->actingAs($auditor)->get(route($routeName))->assertForbidden();
        }
    }

    // ─── module allowlists ───────────────────────────────────────────

    #[Test]
    public function the_module_allowlists_match_the_spec(): void
    {
        // The allowlists are about roles, not tenants, so ask the question on
        // an agency that actually has QMS switched on (card 6afb2e83).
        $agencyId = $this->agency->id;

        // Both roles may audit.
        foreach (['qms_manager', 'internal_auditor'] as $role) {
            $this->assertTrue(ModuleAccess::allows($role, 'audit_trail', $agencyId), "{$role} should read the audit trail.");
            $this->assertTrue(ModuleAccess::allows($role, 'controlled_documents', $agencyId), "{$role} should reach documents.");
            $this->assertTrue(ModuleAccess::allows($role, 'dashboard', $agencyId), "{$role} should reach the dashboard.");
        }

        // Neither reaches the core operational modules.
        foreach (['qms_manager', 'internal_auditor'] as $role) {
            foreach (['applicants', 'receivable', 'expense_request', 'settings', 'users'] as $module) {
                $this->assertFalse(ModuleAccess::allows($role, $module, $agencyId), "{$role} must not reach {$module}.");
            }
        }
    }

    #[Test]
    public function the_qms_roles_are_not_treated_as_rest_of_account(): void
    {
        $this->assertFalse(ModuleAccess::isRestOfAccount('qms_manager'));
        $this->assertFalse(ModuleAccess::isRestOfAccount('internal_auditor'));

        // And the existing classification is untouched.
        $this->assertTrue(ModuleAccess::isRestOfAccount('staff'));
        $this->assertFalse(ModuleAccess::isRestOfAccount('admin'));
    }

    // ─── existing roles unaffected ───────────────────────────────────

    #[Test]
    public function a_staff_user_still_cannot_reach_document_control(): void
    {
        $this->actingAs($this->user('staff'))
            ->get(route('controlled_documents.index'))
            ->assertForbidden();
    }

    #[Test]
    public function an_admin_still_has_full_document_control(): void
    {
        $this->actingAs($this->user('admin'))
            ->get(route('controlled_documents.index'))
            ->assertOk();
    }

    #[Test]
    public function the_audit_trail_still_refuses_ordinary_roles(): void
    {
        foreach (['staff', 'billing', 'recruitment'] as $type) {
            $this->actingAs($this->user($type))
                ->get(route('audit_trail.index'))
                ->assertForbidden();
        }
    }

    #[Test]
    public function the_auditor_role_is_assignable_through_the_users_screen(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->post(route('users.store'), [
            'name'      => 'Aud',
            'surname'   => 'Itor',
            'email'     => 'auditor.qms09@landas.test',
            'username'  => 'auditor.qms09',
            'password'  => 'Secret!2026abc',
            'password_confirmation' => 'Secret!2026abc',
            'user_type' => 'internal_auditor',
            'status'    => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'email'     => 'auditor.qms09@landas.test',
            'user_type' => 'internal_auditor',
        ]);
    }
}
