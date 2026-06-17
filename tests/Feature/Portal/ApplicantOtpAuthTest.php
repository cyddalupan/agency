<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\Applicant;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantOtpAuthTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'applicant@test.com',
        ]);
    }

    // === OTP Login Page ===

    #[Test]
    public function otp_login_page_is_accessible(): void
    {
        $response = $this->get(route('portal.login.otp'));

        $response->assertOk();
        $response->assertSee('OTP');
        $response->assertSee('Email');
    }

    // === OTP Request ===

    #[Test]
    public function applicant_can_request_otp_via_email(): void
    {
        $response = $this->post(route('portal.login.otp.send'), [
            'email' => 'applicant@test.com',
        ]);

        $response->assertStatus(200);
        $response->assertSee('OTP sent');
    }

    #[Test]
    public function otp_request_returns_error_for_nonexistent_email(): void
    {
        $response = $this->post(route('portal.login.otp.send'), [
            'email' => 'nonexistent@test.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function otp_request_requires_email(): void
    {
        $response = $this->post(route('portal.login.otp.send'), []);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function otp_is_stored_in_cache_with_ttl(): void
    {
        $this->post(route('portal.login.otp.send'), [
            'email' => 'applicant@test.com',
        ]);

        $cacheKey = 'otp:applicant:applicant@test.com';
        $this->assertNotNull(Cache::get($cacheKey));
        $this->assertIsString(Cache::get($cacheKey));
    }

    #[Test]
    public function otp_has_six_digit_format(): void
    {
        $this->post(route('portal.login.otp.send'), [
            'email' => 'applicant@test.com',
        ]);

        $cacheKey = 'otp:applicant:applicant@test.com';
        $otp = Cache::get($cacheKey);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);
    }

    // === OTP Verification ===

    #[Test]
    public function applicant_can_login_with_valid_otp(): void
    {
        // First request an OTP
        $this->post(route('portal.login.otp.send'), [
            'email' => 'applicant@test.com',
        ]);

        $cacheKey = 'otp:applicant:applicant@test.com';
        $otp = Cache::get($cacheKey);

        // Now verify with the OTP
        $response = $this->post(route('portal.login.otp.verify'), [
            'email' => 'applicant@test.com',
            'otp'   => $otp,
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($this->applicant, 'applicant');
    }

    #[Test]
    public function applicant_cannot_login_with_invalid_otp(): void
    {
        $this->post(route('portal.login.otp.send'), [
            'email' => 'applicant@test.com',
        ]);

        $response = $this->post(route('portal.login.otp.verify'), [
            'email' => 'applicant@test.com',
            'otp'   => '000000',
        ]);

        $response->assertSessionHasErrors('otp');
        $this->assertGuest('applicant');
    }

    #[Test]
    public function otp_is_invalidated_after_successful_use(): void
    {
        $this->post(route('portal.login.otp.send'), [
            'email' => 'applicant@test.com',
        ]);

        $cacheKey = 'otp:applicant:applicant@test.com';
        $otp = Cache::get($cacheKey);

        // First login with OTP - should succeed
        $this->post(route('portal.login.otp.verify'), [
            'email' => 'applicant@test.com',
            'otp'   => $otp,
        ]);

        // Logout
        $this->post(route('portal.logout'));

        // Try using same OTP again - should fail
        $response = $this->post(route('portal.login.otp.verify'), [
            'email' => 'applicant@test.com',
            'otp'   => $otp,
        ]);

        $response->assertSessionHasErrors('otp');
    }

    #[Test]
    public function otp_expires_after_ttl(): void
    {
        $this->post(route('portal.login.otp.send'), [
            'email' => 'applicant@test.com',
        ]);

        $cacheKey = 'otp:applicant:applicant@test.com';
        $otp = Cache::get($cacheKey);

        // Simulate TTL expiry
        Cache::forget($cacheKey);

        $response = $this->post(route('portal.login.otp.verify'), [
            'email' => 'applicant@test.com',
            'otp'   => $otp,
        ]);

        $response->assertSessionHasErrors('otp');
        $this->assertGuest('applicant');
    }

    #[Test]
    public function otp_login_shows_link_on_regular_login_page(): void
    {
        $response = $this->get(route('portal.login'));

        $response->assertOk();
        $response->assertSee('Login with OTP');
        $response->assertSee(route('portal.login.otp'));
    }
}
