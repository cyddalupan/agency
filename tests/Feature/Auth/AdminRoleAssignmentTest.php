<?php

namespace Tests\Feature\Auth;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminRoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->adminUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    // ─── LIST USERS ─────────────────────────────────────────────────

    #[Test]
    public function admin_can_view_users_list(): void
    {
        User::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('users.index'));

        $response->assertOk();
        $response->assertSee($this->adminUser->name);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_users_list(): void
    {
        $response = $this->get(route('users.index'));

        $response->assertRedirect(route('login'));
    }

    // ─── VIEW USER ──────────────────────────────────────────────────

    #[Test]
    public function admin_can_view_user_details(): void
    {
        $targetUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'coordinator',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('users.show', $targetUser));

        $response->assertOk();
        $response->assertSee($targetUser->name);
        $response->assertSee($targetUser->user_type);
    }

    // ─── EDIT USER ROLE ─────────────────────────────────────────────

    #[Test]
    public function admin_can_view_edit_user_form(): void
    {
        $targetUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('users.edit', $targetUser));

        $response->assertOk();
        $response->assertSee('user_type');
        $response->assertSee('status');
    }

    #[Test]
    public function admin_can_change_user_role(): void
    {
        $targetUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('users.update', $targetUser), [
                'name'      => $targetUser->name,
                'email'     => $targetUser->email,
                'user_type' => 'manager',
                'status'    => 'active',
            ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'        => $targetUser->id,
            'user_type' => 'manager',
        ]);
    }

    #[Test]
    public function admin_can_change_user_status(): void
    {
        $targetUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'status'    => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('users.update', $targetUser), [
                'name'      => $targetUser->name,
                'email'     => $targetUser->email,
                'user_type' => $targetUser->user_type,
                'status'    => 'suspended',
            ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id'     => $targetUser->id,
            'status' => 'suspended',
        ]);
    }

    // ─── ROLE-BASED RESTRICTIONS ────────────────────────────────────

    #[Test]
    public function non_admin_cannot_access_users_list(): void
    {
        $staffUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($staffUser)
            ->get(route('users.index'));

        $response->assertForbidden();
    }

    #[Test]
    public function non_admin_cannot_change_user_roles(): void
    {
        $staffUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
            'status'    => 'active',
        ]);

        $targetUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'coordinator',
        ]);

        $response = $this->actingAs($staffUser)
            ->put(route('users.update', $targetUser), [
                'name'      => $targetUser->name,
                'email'     => $targetUser->email,
                'user_type' => 'admin',
                'status'    => 'active',
            ]);

        $response->assertForbidden();
    }

    // ─── CREATE USER ────────────────────────────────────────────────

    #[Test]
    public function admin_can_access_create_user_form(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('users.create'));

        $response->assertOk();
        $response->assertSee('name');
        $response->assertSee('email');
        $response->assertSee('password');
        $response->assertSee('user_type');
    }

    #[Test]
    public function admin_can_create_new_user(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('users.store'), [
                'name'                  => 'New Staff',
                'email'                 => 'newstaff@test.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'user_type'             => 'staff',
                'status'                => 'active',
            ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email'     => 'newstaff@test.com',
            'user_type' => 'staff',
            'status'    => 'active',
            'agency_id' => $this->agency->id,
        ]);
    }

    #[Test]
    public function admin_cannot_create_user_without_password(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('users.store'), [
                'name'      => 'No Password',
                'email'     => 'nopass@test.com',
                'user_type' => 'staff',
            ]);

        $response->assertSessionHasErrors('password');
    }

    // ─── DELETE USER ────────────────────────────────────────────────

    #[Test]
    public function admin_can_delete_user(): void
    {
        $targetUser = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('users.destroy', $targetUser));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);
    }

    #[Test]
    public function admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->delete(route('users.destroy', $this->adminUser));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $this->adminUser->id,
        ]);
    }
}
