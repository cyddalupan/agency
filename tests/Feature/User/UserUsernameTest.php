<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits: users should support a username.
 *  1. username input on add + edit user pages
 *  2. login works with both email and username
 * (users.username column already exists — nullable, unique.)
 */
class UserUsernameTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'email'     => 'admin@test.com',
            'username'  => 'adminuser',
            'password'  => bcrypt('secret-password'),
        ]);
    }

    // ─── 1. USERNAME INPUT ON ADD / EDIT ─────────────────────────────

    #[Test]
    public function create_user_form_has_username_input(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('users.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="username"', $html);
        $this->assertStringContainsString('Username', $html);
    }

    #[Test]
    public function store_persists_username(): void
    {
        $this->actingAs($this->admin)->post(route('users.store'), [
            'name'       => 'New User',
            'email'      => 'new@test.com',
            'username'   => 'newuser',
            'password'   => 'password123',
            'password_confirmation' => 'password123',
            'user_type'  => 'staff',
            'status'     => 'active',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email'    => 'new@test.com',
            'username' => 'newuser',
        ]);
    }

    #[Test]
    public function store_rejects_duplicate_username(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name'       => 'New User',
            'email'      => 'new@test.com',
            'username'   => 'adminuser', // already taken
            'password'   => 'password123',
            'password_confirmation' => 'password123',
            'user_type'  => 'staff',
            'status'     => 'active',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertDatabaseMissing('users', ['email' => 'new@test.com']);
    }

    #[Test]
    public function edit_user_form_shows_existing_username(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('users.edit', $this->admin))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="username"', $html);
        $this->assertStringContainsString('value="adminuser"', $html);
    }

    #[Test]
    public function update_persists_username_change(): void
    {
        $this->actingAs($this->admin)->put(route('users.update', $this->admin), [
            'name'      => 'Admin User',
            'email'     => 'admin@test.com',
            'username'  => 'adminuser2',
            'user_type' => 'admin',
            'status'    => 'active',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id'       => $this->admin->id,
            'username' => 'adminuser2',
        ]);
    }

    #[Test]
    public function update_allows_keeping_own_username(): void
    {
        $this->actingAs($this->admin)->put(route('users.update', $this->admin), [
            'name'      => 'Admin User',
            'email'     => 'admin@test.com',
            'username'  => 'adminuser', // unchanged — must not fail unique self-check
            'user_type' => 'admin',
            'status'    => 'active',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id'       => $this->admin->id,
            'username' => 'adminuser',
        ]);
    }

    #[Test]
    public function update_rejects_username_taken_by_another_user(): void
    {
        User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'other@test.com',
            'username'  => 'otheruser',
        ]);

        $response = $this->actingAs($this->admin)->put(route('users.update', $this->admin), [
            'name'      => 'Admin User',
            'email'     => 'admin@test.com',
            'username'  => 'otheruser',
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertDatabaseHas('users', [
            'id'       => $this->admin->id,
            'username' => 'adminuser',
        ]);
    }

    // ─── 2. LOGIN WITH EMAIL OR USERNAME ─────────────────────────────

    #[Test]
    public function login_still_works_with_email(): void
    {
        $this->post(route('login'), [
            'email'    => 'admin@test.com',
            'password' => 'secret-password',
        ])->assertRedirect();

        $this->assertAuthenticated();
    }

    #[Test]
    public function login_works_with_username(): void
    {
        $this->post(route('login'), [
            'email'    => 'adminuser',
            'password' => 'secret-password',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($this->admin);
    }

    #[Test]
    public function agency_login_works_with_username(): void
    {
        $this->post(route('agency.login.post'), [
            'email'    => 'adminuser',
            'password' => 'secret-password',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($this->admin);
    }

    #[Test]
    public function login_with_unknown_username_fails(): void
    {
        $this->post(route('login'), [
            'email'    => 'nouser',
            'password' => 'secret-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function login_with_username_and_wrong_password_fails(): void
    {
        $this->post(route('login'), [
            'email'    => 'adminuser',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
