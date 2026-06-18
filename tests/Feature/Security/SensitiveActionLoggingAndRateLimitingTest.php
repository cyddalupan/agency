<?php

namespace Tests\Feature\Security;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SensitiveActionLoggingAndRateLimitingTest extends TestCase
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
            'email'     => 'admin@test.com',
            'password'  => bcrypt('correct-password'),
            'user_type' => 'admin',
            'status'    => 'active',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════
    // 1. SENSITIVE ACTION LOGGING
    // ═══════════════════════════════════════════════════════════════════

    // ─── LOGIN LOGGING ───────────────────────────────────────────────

    #[Test]
    public function admin_login_is_logged_as_sensitive_action(): void
    {
        $this->post(route('login'), [
            'email'    => 'admin@test.com',
            'password' => 'correct-password',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'action'       => 'login',
            'subject_type' => User::class,
            'subject_id'   => $this->admin->id,
        ]);
    }

    #[Test]
    public function applicant_login_is_logged_as_sensitive_action(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'applicant@test.com',
            'password'  => bcrypt('correct-password'),
        ]);

        $this->post(route('portal.login.post'), [
            'email'    => 'applicant@test.com',
            'password' => 'correct-password',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'action'       => 'login',
            'subject_type' => Applicant::class,
            'subject_id'   => $applicant->id,
        ]);
    }

    #[Test]
    public function employer_login_is_logged_as_sensitive_action(): void
    {
        $employer = User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'employer@test.com',
            'password'  => bcrypt('correct-password'),
            'user_type' => 'employer',
            'status'    => 'active',
        ]);

        $this->post(route('employer.login.post'), [
            'email'    => 'employer@test.com',
            'password' => 'correct-password',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'action'       => 'login',
            'subject_type' => User::class,
            'subject_id'   => $employer->id,
        ]);
    }

    #[Test]
    public function admin_logout_is_logged_as_sensitive_action(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('logout'));

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'action'       => 'logout',
        ]);
    }

    #[Test]
    public function applicant_logout_is_logged(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'applicant@test.com',
            'password'  => bcrypt('correct-password'),
        ]);

        $this->actingAs($applicant, 'applicant');
        $this->post(route('portal.logout'));

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'action'       => 'logout',
            'subject_type' => Applicant::class,
            'subject_id'   => $applicant->id,
        ]);
    }

    #[Test]
    public function employer_logout_is_logged(): void
    {
        $employer = User::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'employer@test.com',
            'password'  => bcrypt('correct-password'),
            'user_type' => 'employer',
            'status'    => 'active',
        ]);

        $this->actingAs($employer);
        $this->post(route('employer.logout'));

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'action'       => 'logout',
            'subject_type' => User::class,
            'subject_id'   => $employer->id,
        ]);
    }

    #[Test]
    public function failed_login_attempt_is_logged(): void
    {
        $this->post(route('login'), [
            'email'    => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'agency_id' => $this->agency->id,
            'action'    => 'failed_login',
        ]);
    }

    #[Test]
    public function login_log_contains_ip_address_in_metadata(): void
    {
        $this->post(route('login'), [
            'email'    => 'admin@test.com',
            'password' => 'correct-password',
        ]);

        $log = ActivityLog::where('action', 'login')->latest()->first();
        $this->assertNotNull($log);
        $this->assertArrayHasKey('ip', $log->metadata ?? []);
        $this->assertArrayHasKey('user_agent', $log->metadata ?? []);
    }

    // ─── ROLE CHANGE LOGGING ─────────────────────────────────────────

    #[Test]
    public function role_change_is_logged_when_user_type_is_updated(): void
    {
        $targetUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($this->admin);
        $this->put(route('users.permissions.update', $targetUser), [
            'user_type'    => 'manager',
            'permissions'  => [],
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'action'       => 'role_changed',
            'subject_type' => User::class,
            'subject_id'   => $targetUser->id,
        ]);
    }

    #[Test]
    public function role_change_log_contains_old_and_new_role_in_metadata(): void
    {
        $targetUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($this->admin);
        $this->put(route('users.permissions.update', $targetUser), [
            'user_type'    => 'manager',
            'permissions'  => [],
        ]);

        $log = ActivityLog::where('action', 'role_changed')->latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('staff', $log->metadata['old_role']);
        $this->assertEquals('manager', $log->metadata['new_role']);
    }

    // ─── DATA EXPORT LOGGING ─────────────────────────────────────────

    #[Test]
    public function applicant_data_export_is_logged(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin);
        $this->get(route('applicants.export'));

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'action'       => 'data_export',
        ]);
    }

    #[Test]
    public function data_export_log_contains_export_type_in_description(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin);
        $this->get(route('applicants.export'));

        $log = ActivityLog::where('action', 'data_export')->latest()->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('applicant', strtolower($log->description));
    }

    // ─── DELETION LOGGING ────────────────────────────────────────────

    #[Test]
    public function applicant_deletion_is_logged(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $this->actingAs($this->admin);
        $this->delete(route('applicants.destroy', $applicant));

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'action'       => 'deleted',
            'subject_type' => Applicant::class,
            'subject_id'   => $applicant->id,
        ]);
    }

    #[Test]
    public function user_deletion_is_logged(): void
    {
        $targetUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($this->admin);
        $this->delete(route('users.destroy', $targetUser));

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'action'       => 'deleted',
            'subject_type' => User::class,
            'subject_id'   => $targetUser->id,
        ]);
    }

    // ─── AGENCY STATUS CHANGE LOGGING ────────────────────────────────

    #[Test]
    public function agency_activation_is_logged(): void
    {
        $this->actingAs($this->admin);
        $this->put(route('agencies.activate', $this->agency));

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'action'       => 'agency_activated',
            'subject_type' => Agency::class,
            'subject_id'   => $this->agency->id,
        ]);
    }

    #[Test]
    public function agency_deactivation_is_logged(): void
    {
        $this->actingAs($this->admin);
        $this->put(route('agencies.deactivate', $this->agency));

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'action'       => 'agency_deactivated',
            'subject_type' => Agency::class,
            'subject_id'   => $this->agency->id,
        ]);
    }

    // ─── PASSWORD RESET LOGGING ──────────────────────────────────────

    #[Test]
    public function password_reset_request_is_logged(): void
    {
        $this->post(route('password.email'), [
            'email' => 'admin@test.com',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'agency_id' => $this->agency->id,
            'action'    => 'password_reset_requested',
            'description' => $this->admin->name . ' requested a password reset link.',
        ]);
    }

    #[Test]
    public function password_reset_completion_is_logged_when_reset(): void
    {
        $this->post(route('password.email'), ['email' => 'admin@test.com']);
        $token = resolve(\App\Models\User::class)
            ->where('email', 'admin@test.com')
            ->first()
            ?->getRememberToken() ?? 'fake-token';

        $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => 'admin@test.com',
            'password'              => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'agency_id' => $this->agency->id,
            'action'    => 'password_reset',
            'subject_type' => User::class,
        ]);
    }

    // ─── APPLICANT STATUS UPDATE LOGGING ─────────────────────────────

    #[Test]
    public function applicant_status_update_is_logged(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);
        $applicant = Applicant::first();

        $this->actingAs($this->admin);
        $this->patch(route('applicants.status', $applicant), [
            'status_code' => 6, // Selected
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'action'       => 'status_changed',
            'subject_type' => Applicant::class,
            'subject_id'   => $applicant->id,
        ]);
    }

    #[Test]
    public function applicant_status_change_log_contains_old_and_new_status(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'status_code' => 0, // Pending
        ]);
        $applicant = Applicant::first();

        $this->actingAs($this->admin);
        $this->patch(route('applicants.status', $applicant), [
            'status_code' => 6, // Selected
        ]);

        $log = ActivityLog::where('action', 'status_changed')->latest()->first();
        $this->assertNotNull($log);
        $this->assertArrayHasKey('old_status', $log->metadata);
        $this->assertArrayHasKey('new_status', $log->metadata);
    }

    // ═══════════════════════════════════════════════════════════════════
    // 2. API RATE LIMITING ON AUTH ENDPOINTS
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function password_reset_email_is_rate_limited(): void
    {
        // 6 rapid password reset requests should throttle
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post(route('password.email'), [
                'email' => 'admin@test.com',
            ]);
        }

        $response->assertSessionHasErrors('email');
        $error = session('errors')->get('email')[0] ?? '';
        $this->assertStringContainsStringIgnoringCase('too many', $error);
    }

    #[Test]
    public function otp_send_is_rate_limited(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'applicant@test.com',
        ]);

        for ($i = 0; $i < 6; $i++) {
            $response = $this->post(route('portal.login.otp.send'), [
                'email' => 'applicant@test.com',
            ]);
        }

        $response->assertSessionHasErrors('email');
        $error = session('errors')->get('email')[0] ?? '';
        $this->assertStringContainsStringIgnoringCase('too many', $error);
    }

    #[Test]
    public function otp_verify_is_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post(route('portal.login.otp.verify'), [
                'email' => 'applicant@test.com',
                'otp'   => '000000',
            ]);
        }

        $response->assertSessionHasErrors();
    }

    #[Test]
    public function case_api_endpoints_have_rate_limiting(): void
    {
        $this->actingAs($this->admin, 'web');

        for ($i = 0; $i < 60; $i++) {
            $response = $this->getJson(route('api.cases.search', ['q' => 'test']));
        }

        // 60th request in a single test should exceed API rate limit
        $response->assertStatus(429);
    }

    #[Test]
    public function case_store_endpoint_is_rate_limited(): void
    {
        $this->actingAs($this->admin, 'web');

        for ($i = 0; $i < 30; $i++) {
            $response = $this->postJson(route('api.cases.store'), [
                'title'       => 'Test Case ' . $i,
                'description' => 'Test description',
                'status'      => 'open',
                'priority'    => 'medium',
            ]);
        }

        // 30th POST to store should hit rate limit
        $response->assertStatus(429);
    }

    #[Test]
    public function different_auth_endpoints_have_independent_rate_limits(): void
    {
        // Hit login rate limit for admin
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post(route('login'), [
                'email'    => 'admin@test.com',
                'password' => 'wrong-password',
            ]);
        }

        // Login should be throttled
        $response->assertSessionHasErrors('email');
        $error = session('errors')->get('email')[0] ?? '';
        $this->assertStringContainsStringIgnoringCase('too many', $error);

        // But password reset should still work (independent rate limit)
        $response = $this->post(route('password.email'), [
            'email' => 'admin@test.com',
        ]);

        // Should NOT error (password reset has its own limit)
        $response->assertSessionMissing('email');
    }

    // ═══════════════════════════════════════════════════════════════════
    // 3. SUSPICIOUS ACTIVITY ALERTS
    // ═══════════════════════════════════════════════════════════════════

    #[Test]
    public function rapid_failed_logins_from_multiple_accounts_creates_alert(): void
    {
        // Create several users
        $users = User::factory()->count(5)->create([
            'agency_id' => $this->agency->id,
            'password'  => bcrypt('password'),
        ]);

        // Rapid login attempts across different accounts
        foreach ($users as $user) {
            $this->post(route('login'), [
                'email'    => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        // Should log a suspicious activity alert
        $this->assertDatabaseHas('activity_logs', [
            'agency_id' => $this->agency->id,
            'action'    => 'suspicious_activity',
        ]);
    }

    #[Test]
    public function multiple_concurrent_login_attempts_for_same_user_from_different_sessions_logged(): void
    {
        // Simulate multiple rapid failed logins for same user
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('login'), [
                'email'    => 'admin@test.com',
                'password' => 'wrong-password',
            ]);
        }

        // After 10 failed attempts, a suspicious activity alert should be logged
        $this->assertDatabaseHas('activity_logs', [
            'agency_id' => $this->agency->id,
            'action'    => 'suspicious_activity',
        ]);
    }

    #[Test]
    public function suspicious_activity_log_contains_relevant_metadata(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('login'), [
                'email'    => 'admin@test.com',
                'password' => 'wrong-password',
            ]);
        }

        $log = ActivityLog::where('action', 'suspicious_activity')->latest()->first();
        $this->assertNotNull($log);
        $this->assertArrayHasKey('failed_attempt_count', $log->metadata);
        $this->assertArrayHasKey('target_email', $log->metadata);
    }
}
