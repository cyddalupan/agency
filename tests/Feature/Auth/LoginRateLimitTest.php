<?php

namespace Tests\Feature\Auth;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    #[Test]
    public function admin_login_allows_multiple_attempts_before_throttling(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'email' => 'admin@test.com',
            'password' => bcrypt('correct-password'),
        ]);

        // Attempt with wrong password multiple times
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login'), [
                'email' => 'admin@test.com',
                'password' => 'wrong-password',
            ]);

            if ($i < 4) {
                $response->assertSessionHasErrors('email');
                $response->assertSessionMissing('error');
            }
        }

        // 6th attempt should trigger throttle
        $response = $this->post(route('login'), [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function admin_login_throttle_message_appears_after_too_many_attempts(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'email' => 'admin@test.com',
            'password' => bcrypt('correct-password'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login'), [
                'email' => 'admin@test.com',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('login'), [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');

        // Now even a correct password should be throttled
        $response = $this->post(route('login'), [
            'email' => 'admin@test.com',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function applicant_login_has_rate_limiting(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'email' => 'applicant@test.com',
            'password' => bcrypt('correct-password'),
        ]);

        // 5 wrong attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('portal.login.post'), [
                'email' => 'applicant@test.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be throttled
        $response = $this->post(route('portal.login.post'), [
            'email' => 'applicant@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function employer_login_has_rate_limiting(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'email' => 'employer@test.com',
            'password' => bcrypt('correct-password'),
            'user_type' => 'employer',
        ]);

        // 5 wrong attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('employer.login.post'), [
                'email' => 'employer@test.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be throttled
        $response = $this->post(route('employer.login.post'), [
            'email' => 'employer@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function rate_limit_resets_after_successful_login(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'email' => 'admin@test.com',
            'password' => bcrypt('correct-password'),
        ]);

        // 3 wrong attempts
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('login'), [
                'email' => 'admin@test.com',
                'password' => 'wrong-password',
            ]);
        }

        // Clear the rate limiter hit count (simulating reset)
        RateLimiter::clear('login:admin@test.com');

        // Now correct login should work
        $response = $this->post(route('login'), [
            'email' => 'admin@test.com',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('agency.dashboard'));
    }
}
