<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits request 2026-08-16: admin should be able to change a user's
 * password from the user edit page (e.g. /users/37/edit).
 */
class UserPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $admin;

    private User $target;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
            'password'  => Hash::make('old-password-123'),
        ]);
    }

    #[Test]
    public function edit_page_has_password_fields(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.edit', $this->target));

        $response->assertOk();
        $response->assertSee('name="password"', false);
        $response->assertSee('name="password_confirmation"', false);
    }

    #[Test]
    public function admin_can_change_user_password(): void
    {
        $response = $this->actingAs($this->admin)->put(route('users.update', $this->target), [
            'name'                  => $this->target->name,
            'email'                 => $this->target->email,
            'user_type'             => $this->target->user_type,
            'status'                => $this->target->status,
            'password'              => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertTrue(Hash::check('new-password-456', $this->target->fresh()->password));
        $this->assertFalse(Hash::check('old-password-123', $this->target->fresh()->password));
    }

    #[Test]
    public function empty_password_leaves_existing_password_unchanged(): void
    {
        $response = $this->actingAs($this->admin)->put(route('users.update', $this->target), [
            'name'      => $this->target->name,
            'email'     => $this->target->email,
            'user_type' => $this->target->user_type,
            'status'    => $this->target->status,
            'password'  => '',
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertTrue(Hash::check('old-password-123', $this->target->fresh()->password));
    }

    #[Test]
    public function password_still_requires_confirmation_but_allows_simple_passwords(): void
    {
        // Toybits 2026-08-16: min:8 removed — really simple passwords allowed.
        $response = $this->actingAs($this->admin)->put(route('users.update', $this->target), [
            'name'      => $this->target->name,
            'email'     => $this->target->email,
            'user_type' => $this->target->user_type,
            'status'    => $this->target->status,
            'password'  => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertTrue(Hash::check('short', $this->target->fresh()->password));
    }

    #[Test]
    public function password_mismatch_is_still_rejected(): void
    {
        $response = $this->actingAs($this->admin)->put(route('users.update', $this->target), [
            'name'      => $this->target->name,
            'email'     => $this->target->email,
            'user_type' => $this->target->user_type,
            'status'    => $this->target->status,
            'password'  => 'simple',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertTrue(Hash::check('old-password-123', $this->target->fresh()->password));
    }

    #[Test]
    public function admin_cannot_change_password_of_user_in_other_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherUser = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'password'  => Hash::make('other-old-pass'),
        ]);

        $response = $this->actingAs($this->admin)->put(route('users.update', $otherUser), [
            'name'      => $otherUser->name,
            'email'     => $otherUser->email,
            'user_type' => $otherUser->user_type,
            'status'    => $otherUser->status,
            'password'  => 'hacked-pass-789',
        ]);

        $response->assertForbidden();
        $this->assertTrue(Hash::check('other-old-pass', $otherUser->fresh()->password));
    }
}
