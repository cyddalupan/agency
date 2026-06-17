<?php

namespace Tests\Feature\Auth;

use App\Models\Agency;
use App\Models\User;
use App\Models\Employer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    // ─── ROLE MIDDLEWARE: ADMIN ──────────────────────────────────────

    #[Test]
    public function admin_can_access_admin_routes(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(route('employers.index'));

        $response->assertOk();
    }

    #[Test]
    public function admin_can_access_billing_routes(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(route('bills.index'));

        $response->assertOk();
    }

    // ─── ROLE-BASED ACCESS RESTRICTIONS ─────────────────────────────

    #[Test]
    public function billing_user_can_access_billing_routes(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);

        $response = $this->actingAs($user)->get(route('bills.index'));

        $response->assertOk();
    }

    #[Test]
    public function billing_user_cannot_access_applicant_crud_routes(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);

        $response = $this->actingAs($user)->get(route('applicants.index'));

        $response->assertForbidden();
    }

    #[Test]
    public function staff_user_cannot_access_billing_routes(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($user)->get(route('bills.index'));

        $response->assertForbidden();
    }

    #[Test]
    public function report_viewer_can_only_access_report_routes(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'report_viewer',
        ]);

        $response = $this->actingAs($user)->get(route('employers.index'));

        // Report viewers should not have access to CRUD pages
        $response->assertForbidden();
    }

    #[Test]
    public function recruiter_can_access_applicant_routes(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'recruiter',
        ]);

        $response = $this->actingAs($user)->get(route('applicants.index'));

        $response->assertOk();
    }

    #[Test]
    public function recruiter_cannot_access_billing_routes(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'recruiter',
        ]);

        $response = $this->actingAs($user)->get(route('bills.index'));

        $response->assertForbidden();
    }

    #[Test]
    public function processor_cannot_access_employer_routes(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'processor',
        ]);

        $response = $this->actingAs($user)->get(route('employers.index'));

        $response->assertForbidden();
    }

    // ─── SUPER ADMIN ACCESS ─────────────────────────────────────────

    #[Test]
    public function super_admin_can_access_all_routes(): void
    {
        $user = User::factory()->create([
            'agency_id' => null,
            'user_type' => 'super_admin',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    // ─── GUEST RESTRICTIONS ─────────────────────────────────────────

    #[Test]
    public function unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('employers.index'));

        $response->assertRedirect(route('login'));
    }

    // ─── MIDDLEWARE REGISTRATION ────────────────────────────────────

    #[Test]
    public function role_middleware_is_registered_in_kernel(): void
    {
        $middleware = app('router')->getMiddleware();

        $this->assertArrayHasKey('role', $middleware,
            'The role middleware alias "role" must be registered in bootstrap/app.php'
        );
    }

    #[Test]
    public function role_or_middleware_is_registered(): void
    {
        $middleware = app('router')->getMiddleware();

        // Either a generic 'role' middleware or specific role aliases should be registered
        $hasRoleMiddleware = isset($middleware['role']);
        $hasCanMiddleware = isset($middleware['can']);

        $this->assertTrue($hasRoleMiddleware,
            'The role middleware alias "role" must be registered in bootstrap/app.php'
        );
    }
}
