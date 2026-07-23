<?php

namespace Tests\Feature\Sponsor;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SponsorForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create([
            'status' => 'active',
        ]);
    }

    #[Test]
    public function forgot_password_page_is_accessible(): void
    {
        $response = $this->get(route('sponsor.password.request'));

        $response->assertOk();
        $response->assertSee('Forgot');
        $response->assertSee('Password');
    }

    #[Test]
    public function forgot_password_form_has_email_field(): void
    {
        $response = $this->get(route('sponsor.password.request'));

        $response->assertOk();
        $response->assertSee('email', false);
    }

    #[Test]
    public function forgot_password_request_requires_email(): void
    {
        $response = $this->post(route('sponsor.password.email'), [
            'email' => '',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function forgot_password_sends_reset_link_for_valid_sponsor(): void
    {
        $user = User::create([
            'agency_id' => $this->agency->id,
            'name'      => 'Test Sponsor',
            'email'     => 'sponsor@test.com',
            'username'  => 'SPONSOR-001',
            'password'  => bcrypt('password'),
            'user_type' => 'sponsor',
            'status'    => 'active',
        ]);

        $response = $this->post(route('sponsor.password.email'), [
            'email' => 'sponsor@test.com',
        ]);

        $response->assertSessionHas('status');
    }

    #[Test]
    public function sponsor_can_reset_password_with_valid_token(): void
    {
        $user = User::create([
            'agency_id' => $this->agency->id,
            'name'      => 'Test Sponsor',
            'email'     => 'sponsor@test.com',
            'username'  => 'SPONSOR-001',
            'password'  => bcrypt('password'),
            'user_type' => 'sponsor',
            'status'    => 'active',
        ]);

        // Create a reset token using the Password facade (handles hashing)
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $response = $this->post(route('sponsor.password.update'), [
            'token'                 => $token,
            'email'                 => 'sponsor@test.com',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('sponsor.login'));

        // Verify new password works
        $response = $this->post(route('sponsor.login.post'), [
            'login'    => 'SPONSOR-001',
            'password' => 'newpassword123',
        ]);

        $response->assertRedirect(route('sponsor.dashboard'));
    }

    #[Test]
    public function forgot_password_does_not_leak_if_email_not_found(): void
    {
        $response = $this->post(route('sponsor.password.email'), [
            'email' => 'nonexistent@test.com',
        ]);

        // Should not leak whether the email exists
        $response->assertSessionHas('status');
    }

    #[Test]
    public function password_reset_form_is_accessible_with_valid_token(): void
    {
        $user = User::create([
            'agency_id' => $this->agency->id,
            'name'      => 'Test Sponsor',
            'email'     => 'sponsor@test.com',
            'username'  => 'SPONSOR-001',
            'password'  => bcrypt('password'),
            'user_type' => 'sponsor',
            'status'    => 'active',
        ]);

        // Generate a token using the Password facade (this also stores the hash in DB)
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $response = $this->get(route('sponsor.password.reset', ['token' => $token, 'email' => 'sponsor@test.com']));

        $response->assertOk();
        $response->assertSee('Reset');
        $response->assertSee('Password');
    }

    #[Test]
    public function forgot_password_link_is_on_login_page(): void
    {
        $response = $this->get(route('sponsor.login'));

        $response->assertOk();
        $response->assertSee('Forgot');
    }
}
