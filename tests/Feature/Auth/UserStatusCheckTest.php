<?php

namespace Tests\Feature\Auth;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserStatusCheckTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    // ─── ADMIN LOGIN STATUS CHECKS ───────────────────────────────────

    #[Test]
    public function active_admin_user_can_login(): void
    {
        User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'admin@test.com',
            'password'  => bcrypt('correct-password'),
            'status'    => 'active',
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'admin@test.com',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('agency.dashboard'));
        $this->assertAuthenticated();
    }

    #[Test]
    public function suspended_admin_user_cannot_login(): void
    {
        User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'suspended@test.com',
            'password'  => bcrypt('correct-password'),
            'status'    => 'suspended',
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'suspended@test.com',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function inactive_admin_user_cannot_login(): void
    {
        User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'inactive@test.com',
            'password'  => bcrypt('correct-password'),
            'status'    => 'inactive',
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'inactive@test.com',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function suspended_admin_user_sees_appropriate_error_message(): void
    {
        User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'suspended@test.com',
            'password'  => bcrypt('correct-password'),
            'status'    => 'suspended',
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'suspended@test.com',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('email');
        $errors = session('errors');
        $this->assertStringContainsString(
            'suspended',
            strtolower($errors->first('email')),
            'Error message should mention the account is suspended'
        );
    }

    #[Test]
    public function inactive_admin_user_sees_appropriate_error_message(): void
    {
        User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'inactive@test.com',
            'password'  => bcrypt('correct-password'),
            'status'    => 'inactive',
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'inactive@test.com',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('email');
        $errors = session('errors');
        $this->assertStringContainsString(
            'inactive',
            strtolower($errors->first('email')),
            'Error message should mention the account is inactive'
        );
    }

    // ─── EMPLOYER LOGIN STATUS CHECKS ────────────────────────────────

    #[Test]
    public function active_employer_user_can_login(): void
    {
        User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'employer@test.com',
            'password'  => bcrypt('correct-password'),
            'user_type' => 'employer',
            'status'    => 'active',
        ]);

        $response = $this->post(route('employer.login.post'), [
            'email'    => 'employer@test.com',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('employer.dashboard'));
        $this->assertAuthenticated();
    }

    #[Test]
    public function suspended_employer_user_cannot_login(): void
    {
        User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'employer@test.com',
            'password'  => bcrypt('correct-password'),
            'user_type' => 'employer',
            'status'    => 'suspended',
        ]);

        $response = $this->post(route('employer.login.post'), [
            'email'    => 'employer@test.com',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function inactive_employer_user_cannot_login(): void
    {
        User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'employer@test.com',
            'password'  => bcrypt('correct-password'),
            'user_type' => 'employer',
            'status'    => 'inactive',
        ]);

        $response = $this->post(route('employer.login.post'), [
            'email'    => 'employer@test.com',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
