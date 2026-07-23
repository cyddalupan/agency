<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FraPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email'     => 'cyd@test.com',
            'username'  => 'toybits',
            'password'  => Hash::make('old-password-123'),
            'user_type' => 'employer',
            'status'    => 'active',
        ]);
    }

    #[Test]
    public function reset_then_login_with_username(): void
    {
        $this->from(route('fra.password.email'))
            ->post(route('fra.password.email'), [
                'email' => 'cyd@test.com',
            ])
            ->assertSessionHas('status');

        $rawToken = Password::broker()->createToken($this->user);
        $newPassword = 'fresh-password-456';

        $this->from(route('fra.password.reset', ['token' => $rawToken]))
            ->post(route('fra.password.update'), [
                'token'                 => $rawToken,
                'email'                 => 'cyd@test.com',
                'password'              => $newPassword,
                'password_confirmation' => $newPassword,
            ])
            ->assertRedirect(route('fra.login'));

        // Login with username works
        $this->post(route('fra.login.post'), [
            'login'    => 'toybits',
            'password' => $newPassword,
        ])->assertRedirect(route('fra.dashboard'));

        $this->assertAuthenticated();
    }

    #[Test]
    public function reset_then_login_with_email(): void
    {
        // Run forgot password flow
        $this->from(route('fra.password.email'))
            ->post(route('fra.password.email'), [
                'email' => 'cyd@test.com',
            ])
            ->assertSessionHas('status');

        $rawToken = Password::broker()->createToken($this->user);
        $newPassword = 'another-fresh-pw-789';

        $this->from(route('fra.password.reset', ['token' => $rawToken]))
            ->post(route('fra.password.update'), [
                'token'                 => $rawToken,
                'email'                 => 'cyd@test.com',
                'password'              => $newPassword,
                'password_confirmation' => $newPassword,
            ])
            ->assertRedirect(route('fra.login'));

        // Login with email also works
        $this->post(route('fra.login.post'), [
            'login'    => 'cyd@test.com',
            'password' => $newPassword,
        ])->assertRedirect(route('fra.dashboard'));

        $this->assertAuthenticated();
    }

    #[Test]
    public function login_with_wrong_password_fails(): void
    {
        $response = $this->post(route('fra.login.post'), [
            'login'    => 'toybits',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertEquals(
            trans('auth.failed'),
            session('errors')->first('login')
        );
    }

    #[Test]
    public function login_with_email_also_works(): void
    {
        $this->post(route('fra.login.post'), [
            'login'    => 'cyd@test.com',
            'password' => 'old-password-123',
        ])->assertRedirect(route('fra.dashboard'));

        $this->assertAuthenticated();
    }

    #[Test]
    public function login_with_wrong_email_also_fails(): void
    {
        $response = $this->post(route('fra.login.post'), [
            'login'    => 'wrong@email.com',
            'password' => 'old-password-123',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertEquals(
            trans('auth.failed'),
            session('errors')->first('login')
        );
    }

    #[Test]
    public function login_fails_for_non_employer(): void
    {
        User::factory()->create([
            'email'     => 'applicant@test.com',
            'username'  => 'applicant_user',
            'password'  => Hash::make('password-123'),
            'user_type' => 'applicant',
            'status'    => 'active',
        ]);

        $response = $this->post(route('fra.login.post'), [
            'login'    => 'applicant_user',
            'password' => 'password-123',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertEquals(
            trans('auth.failed'),
            session('errors')->first('login')
        );
    }

    #[Test]
    public function login_fails_for_inactive_user(): void
    {
        User::factory()->create([
            'email'     => 'inactive@test.com',
            'username'  => 'inactive_user',
            'password'  => Hash::make('password-123'),
            'user_type' => 'employer',
            'status'    => 'inactive',
        ]);

        $response = $this->post(route('fra.login.post'), [
            'login'    => 'inactive_user',
            'password' => 'password-123',
        ]);

        $response->assertSessionHasErrors('login');
        $errorMsg = session('errors')->first('login');
        $this->assertStringContainsString('inactive', $errorMsg);
        $this->assertNotEquals(trans('auth.failed'), $errorMsg);
    }

    #[Test]
    public function user_with_numeric_status_one_is_treated_as_active(): void
    {
        User::factory()->create([
            'email'     => 'cyds@test.com',
            'username'  => 'cyds_user',
            'password'  => Hash::make('password-123'),
            'user_type' => 'employer',
            'status'    => '1',
        ]);

        $this->post(route('fra.login.post'), [
            'login'    => 'cyds_user',
            'password' => 'password-123',
        ])->assertRedirect(route('fra.dashboard'));

        $this->assertAuthenticated();
    }

    #[Test]
    public function fra_root_redirects_to_login_when_not_authenticated(): void
    {
        $response = $this->get('/fra');

        $response->assertRedirect(route('fra.login'));
    }

    #[Test]
    public function fra_root_redirects_to_dashboard_when_authenticated(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/fra');

        $response->assertRedirect(route('fra.dashboard'));
    }

    #[Test]
    public function hash_integrity_after_reset(): void
    {
        // Direct test that forceFill + Hash::make produces a valid hash
        $newPass = 'direct-test-pass-999';
        $this->user->forceFill([
            'password' => Hash::make($newPass),
        ])->save();

        $this->user->refresh();
        $this->assertTrue(
            Hash::check($newPass, $this->user->password),
            'Hash::check should work after forceFill + Hash::make'
        );
    }
}
