<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * RED PHASE: These tests expect a role/permission assignment UI.
 *
 * None of these routes or models exist yet — every test will FAIL.
 * The GREEN phase will implement:
 *   - GET|POST /users/{user}/permissions
 *   - permissions table + user_permissions pivot table
 *   - Role assignment form with granular permission checkboxes
 *   - Admin-only access with agency scoping
 */
class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    // ─── VIEW PERMISSIONS PAGE ────────────────────────────────────────

    #[Test]
    public function admin_can_view_permissions_page(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.permissions', $target));

        $response->assertOk();
        // The page should show the user's current role
        $response->assertSee($target->name);
        // Should contain a role selector/dropdown
        $response->assertSee('user_type');
    }

    #[Test]
    public function permissions_page_shows_all_available_permissions(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.permissions', $target));

        $response->assertOk();
        // The view should have a list of all available permissions
        $response->assertViewHas('permissions');
    }

    #[Test]
    public function permissions_page_shows_users_currently_assigned_permissions(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.permissions', $target));

        $response->assertOk();
        // The view should contain the user's currently assigned permissions
        $response->assertViewHas('userPermissions');
    }

    // ─── UPDATE ROLE ──────────────────────────────────────────────────

    #[Test]
    public function admin_can_update_user_role(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('users.permissions.update', $target), [
                'user_type' => 'coordinator',
            ]);

        $response->assertRedirect(route('users.permissions', $target));
        $this->assertDatabaseHas('users', [
            'id'        => $target->id,
            'user_type' => 'coordinator',
        ]);
    }

    #[Test]
    public function admin_can_assign_granular_permissions(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($admin)
            ->put(route('users.permissions.update', $target), [
                'user_type'    => 'staff',
                'permissions'  => ['view_applicants', 'edit_applicants'],
            ]);

        $response->assertRedirect(route('users.permissions', $target));
        // Verify the permissions were attached
        $this->assertDatabaseHas('user_permissions', [
            'user_id'       => $target->id,
            'permission'    => 'view_applicants',
        ]);
        $this->assertDatabaseHas('user_permissions', [
            'user_id'       => $target->id,
            'permission'    => 'edit_applicants',
        ]);
    }

    #[Test]
    public function assigning_permissions_replaces_previous_permissions(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        // Assign initial permissions
        $this->actingAs($admin)
            ->put(route('users.permissions.update', $target), [
                'user_type'   => 'staff',
                'permissions' => ['view_applicants'],
            ]);

        // Now replace with a different set
        $this->actingAs($admin)
            ->put(route('users.permissions.update', $target), [
                'user_type'   => 'staff',
                'permissions' => ['view_bills', 'edit_bills'],
            ]);

        // Old permission should be gone
        $this->assertDatabaseMissing('user_permissions', [
            'user_id'    => $target->id,
            'permission' => 'view_applicants',
        ]);
        // New permissions should exist
        $this->assertDatabaseHas('user_permissions', [
            'user_id'    => $target->id,
            'permission' => 'view_bills',
        ]);
    }

    #[Test]
    public function update_role_validates_user_type(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($admin)
            ->put(route('users.permissions.update', $target), [
                'user_type' => '',
            ]);

        $response->assertSessionHasErrors(['user_type']);
    }

    #[Test]
    public function update_role_validates_permissions_are_known(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($admin)
            ->put(route('users.permissions.update', $target), [
                'user_type'   => 'staff',
                'permissions' => ['nonexistent_permission_xyz'],
            ]);

        $response->assertSessionHasErrors(['permissions.0']);
    }

    // ─── ACCESS CONTROL ──────────────────────────────────────────────

    #[Test]
    public function staff_cannot_view_permissions_page(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($staff)
            ->get(route('users.permissions', $target));

        $response->assertForbidden();
    }

    #[Test]
    public function staff_cannot_update_permissions(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($staff)
            ->put(route('users.permissions.update', $target), [
                'user_type' => 'admin',
            ]);

        $response->assertForbidden();
    }

    #[Test]
    public function admin_cannot_view_permissions_of_user_in_other_agency(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $otherAgency = Agency::factory()->create();
        $otherUser = User::factory()->create([
            'agency_id' => $otherAgency->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.permissions', $otherUser));

        $response->assertForbidden();
    }

    #[Test]
    public function admin_cannot_update_permissions_of_user_in_other_agency(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $otherAgency = Agency::factory()->create();
        $otherUser = User::factory()->create([
            'agency_id' => $otherAgency->id,
        ]);

        $response = $this->actingAs($admin)
            ->put(route('users.permissions.update', $otherUser), [
                'user_type' => 'staff',
            ]);

        $response->assertForbidden();
    }

    // ─── SUPER ADMIN ──────────────────────────────────────────────────

    #[Test]
    public function super_admin_can_view_permissions_across_agencies(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $otherAgency = Agency::factory()->create();
        $target = User::factory()->create([
            'agency_id' => $otherAgency->id,
        ]);

        $response = $this->actingAs($superAdmin)
            ->get(route('users.permissions', $target));

        $response->assertOk();
        $response->assertSee($target->name);
    }

    #[Test]
    public function super_admin_can_update_permissions_across_agencies(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $otherAgency = Agency::factory()->create();
        $target = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($superAdmin)
            ->put(route('users.permissions.update', $target), [
                'user_type' => 'manager',
            ]);

        $response->assertRedirect(route('users.permissions', $target));
        $this->assertDatabaseHas('users', [
            'id'        => $target->id,
            'user_type' => 'manager',
        ]);
    }

    // ─── GUEST ACCESS ─────────────────────────────────────────────────

    #[Test]
    public function guest_is_redirected_to_login_when_viewing_permissions(): void
    {
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->get(route('users.permissions', $target));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_is_redirected_to_login_when_updating_permissions(): void
    {
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->put(route('users.permissions.update', $target), [
            'user_type' => 'admin',
        ]);

        $response->assertRedirect(route('login'));
    }
}
