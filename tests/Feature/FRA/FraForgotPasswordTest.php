<?php

namespace Tests\Feature\FRA;

use App\Models\User;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FraForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create([
            'name' => 'Test Agency',
        ]);
    }

    #[Test]
    public function forgot_password_page_is_accessible(): void
    {
        $response = $this->get(route('fra.password.request'));

        $response->assertStatus(200);
        $response->assertSee('Forgot Password');
    }

    #[Test]
    public function forgot_password_page_has_email_field(): void
    {
        $response = $this->get(route('fra.password.request'));

        $response->assertSee('email');
        $response->assertSee('Send Reset Link');
    }

    #[Test]
    public function forgot_password_page_has_back_to_login_link(): void
    {
        $response = $this->get(route('fra.password.request'));

        $response->assertSee(route('fra.login'));
    }

    #[Test]
    public function forgot_password_sends_reset_link_for_valid_email(): void
    {
        $user = User::factory()->create([
            'email' => 'employer@test.com',
            'user_type' => 'employer',
            'agency_id' => $this->agency->id,
            'status' => 'active',
        ]);

        $response = $this->post(route('fra.password.email'), [
            'email' => 'employer@test.com',
        ]);

        $response->assertSessionHas('status');
    }

    #[Test]
    public function forgot_password_rejects_invalid_email(): void
    {
        $response = $this->post(route('fra.password.email'), [
            'email' => 'not-a-user@test.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function forgot_password_requires_email(): void
    {
        $response = $this->post(route('fra.password.email'), [
            'email' => '',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function reset_password_form_is_accessible_with_token(): void
    {
        $response = $this->get(route('fra.password.reset', ['token' => 'test-token-123']));

        $response->assertStatus(200);
        $response->assertSee('Reset Password');
    }

    #[Test]
    public function reset_password_form_has_required_fields(): void
    {
        $response = $this->get(route('fra.password.reset', ['token' => 'test-token-123']));

        $response->assertSee('password');
        $response->assertSee('password_confirmation');
    }

    #[Test]
    public function reset_password_fails_with_missing_fields(): void
    {
        $response = $this->post(route('fra.password.update'), [
            'token' => '',
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['token', 'email', 'password']);
    }

    #[Test]
    public function reset_password_fails_with_unconfirmed_password(): void
    {
        $response = $this->post(route('fra.password.update'), [
            'token' => 'some-token',
            'email' => 'employer@test.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
