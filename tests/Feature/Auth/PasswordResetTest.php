<?php

namespace Tests\Feature\Auth;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'admin@test.com',
            'password'  => Hash::make('old-password'),
        ]);
    }

    // ─── PASSWORD RESET REQUEST ─────────────────────────────────────

    #[Test]
    public function password_reset_request_page_is_accessible(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
        $response->assertSee('email'); // Should have an email field
    }

    #[Test]
    public function password_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), [
            'email' => 'admin@test.com',
        ]);

        $response->assertSessionHas('status');

        // A notification should be sent (or token created in DB)
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'admin@test.com',
        ]);
    }

    #[Test]
    public function password_reset_request_fails_for_nonexistent_email(): void
    {
        $response = $this->post(route('password.email'), [
            'email' => 'nonexistent@test.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // ─── PASSWORD RESET ─────────────────────────────────────────────

    #[Test]
    public function password_reset_form_is_accessible_with_valid_token(): void
    {
        // Create a reset token
        $token = \Illuminate\Support\Facades\Password::createToken($this->user);

        $response = $this->get(route('password.reset', ['token' => $token]));

        $response->assertOk();
        $response->assertSee('email');
        $response->assertSee('password');
        $response->assertSee('password_confirmation');
    }

    #[Test]
    public function password_can_be_reset_with_valid_token(): void
    {
        $token = \Illuminate\Support\Facades\Password::createToken($this->user);

        $response = $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => 'admin@test.com',
            'password'              => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHas('status');

        // User should be able to login with the new password
        $this->post(route('logout')); // Logout any existing session

        $loginResponse = $this->post(route('login'), [
            'email'    => 'admin@test.com',
            'password' => 'new-password',
        ]);

        $loginResponse->assertRedirect(route('agency.dashboard'));
    }

    #[Test]
    public function password_cannot_be_reset_with_invalid_token(): void
    {
        $response = $this->post(route('password.update'), [
            'token'                 => 'invalid-token',
            'email'                 => 'admin@test.com',
            'password'              => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function password_reset_requires_password_confirmation(): void
    {
        $token = \Illuminate\Support\Facades\Password::createToken($this->user);

        $response = $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => 'admin@test.com',
            'password'              => 'new-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function password_reset_requires_minimum_length(): void
    {
        $token = \Illuminate\Support\Facades\Password::createToken($this->user);

        $response = $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => 'admin@test.com',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function employer_user_can_reset_password(): void
    {
        $employerUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'employer@test.com',
            'password'  => Hash::make('old-password'),
            'user_type' => 'employer',
        ]);

        $token = \Illuminate\Support\Facades\Password::createToken($employerUser);

        $response = $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => 'employer@test.com',
            'password'              => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHas('status');

        // Login with new password
        $this->post(route('logout'));
        $loginResponse = $this->post(route('employer.login.post'), [
            'email'    => 'employer@test.com',
            'password' => 'new-password',
        ]);

        $loginResponse->assertRedirect(route('employer.dashboard'));
    }
}
