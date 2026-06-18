<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    // ─── LIST USERS ───────────────────────────────────────────────────

    #[Test]
    public function admin_can_list_users(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        User::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.index'));

        $response->assertOk();
        $response->assertViewHas('users');
        // The paginated list should contain the admin + 3 created users
        $this->assertCount(4, $response->viewData('users'));
    }

    #[Test]
    public function user_list_respects_agency_scoping(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $otherAgency = Agency::factory()->create();
        // User in another agency
        User::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($admin)
            ->get(route('users.index'));

        $response->assertOk();
        $users = $response->viewData('users');
        // Should only see users from the admin's agency (only the admin themselves)
        $this->assertCount(1, $users);
    }

    #[Test]
    public function staff_cannot_list_users(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($staff)
            ->get(route('users.index'));

        $response->assertForbidden();
    }

    // ─── CREATE USER ──────────────────────────────────────────────────

    #[Test]
    public function admin_can_view_create_form(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.create'));

        $response->assertOk();
    }

    #[Test]
    public function admin_can_create_user(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name'                  => 'New Staff User',
            'email'                 => 'staff@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'user_type'             => 'staff',
            'status'                => 'active',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email'     => 'staff@example.com',
            'name'      => 'New Staff User',
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
    }

    #[Test]
    public function create_user_validates_required_fields(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'user_type', 'status']);
    }

    #[Test]
    public function create_user_validates_unique_email(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'existing@example.com',
        ]);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name'                  => 'Duplicate',
            'email'                 => 'existing@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'user_type'             => 'staff',
            'status'                => 'active',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function staff_cannot_create_user(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($staff)->post(route('users.store'), [
            'name'                  => 'Should Fail',
            'email'                 => 'fail@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'user_type'             => 'staff',
            'status'                => 'active',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'fail@example.com']);
    }

    // ─── VIEW USER ────────────────────────────────────────────────────

    #[Test]
    public function admin_can_view_user_details(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.show', $target));

        $response->assertOk();
        $response->assertSee($target->name);
    }

    #[Test]
    public function admin_cannot_view_user_from_other_agency(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $otherAgency = Agency::factory()->create();
        $otherUser = User::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($admin)
            ->get(route('users.show', $otherUser));

        $response->assertForbidden();
    }

    // ─── EDIT / UPDATE USER ──────────────────────────────────────────

    #[Test]
    public function admin_can_view_edit_form(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.edit', $target));

        $response->assertOk();
    }

    #[Test]
    public function admin_can_update_user(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Old Name',
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $target), [
            'name'      => 'Updated Name',
            'email'     => $target->email,
            'user_type' => 'coordinator',
            'status'    => 'active',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id'   => $target->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function update_user_validates_unique_email_except_self(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'email'     => 'admin@example.com',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'target@example.com',
        ]);

        // Updating with the same email should work (ignore current user)
        $response = $this->actingAs($admin)->put(route('users.update', $target), [
            'name'      => 'Still Valid',
            'email'     => 'target@example.com',
            'user_type' => 'staff',
            'status'    => 'active',
        ]);

        $response->assertRedirect(route('users.index'));
    }

    #[Test]
    public function staff_cannot_update_user(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($staff)->put(route('users.update', $target), [
            'name'      => 'Hacked Name',
            'email'     => $target->email,
            'user_type' => 'staff',
            'status'    => 'active',
        ]);

        $response->assertForbidden();
    }

    // ─── DELETE USER ──────────────────────────────────────────────────

    #[Test]
    public function admin_can_delete_other_user(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('users.destroy', $target));

        $response->assertRedirect(route('users.index'));
        $this->assertModelMissing($target);
    }

    #[Test]
    public function admin_cannot_delete_self(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('users.destroy', $admin));

        $response->assertRedirect(route('users.index'));
        $this->assertModelExists($admin);
    }

    #[Test]
    public function staff_cannot_delete_user(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($staff)
            ->delete(route('users.destroy', $target));

        $response->assertForbidden();
    }

    // ─── SUPER ADMIN ──────────────────────────────────────────────────

    #[Test]
    public function super_admin_can_manage_users_across_agencies(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $agencyA = Agency::factory()->create();
        $agencyB = Agency::factory()->create();
        User::factory()->create(['agency_id' => $agencyA->id]);
        User::factory()->create(['agency_id' => $agencyB->id]);

        $response = $this->actingAs($superAdmin)
            ->get(route('users.index'));

        $response->assertOk();
        // Super admin should see users across all agencies
        $users = $response->viewData('users');
        // super admin + 2 users from both agencies = 3
        $this->assertCount(3, $users);
    }

    #[Test]
    public function super_admin_can_view_any_user(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $otherAgency = Agency::factory()->create();
        $target = User::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($superAdmin)
            ->get(route('users.show', $target));

        $response->assertOk();
    }

    #[Test]
    public function super_admin_can_delete_across_agencies(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $otherAgency = Agency::factory()->create();
        $target = User::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($superAdmin)
            ->delete(route('users.destroy', $target));

        $response->assertRedirect(route('users.index'));
        $this->assertModelMissing($target);
    }

    // ─── GUEST ACCESS ─────────────────────────────────────────────────

    #[Test]
    public function guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('users.index'));
        $response->assertRedirect(route('login'));
    }
}
