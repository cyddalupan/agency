<?php

namespace Tests\Feature\Applicant;

use App\Http\Controllers\ApplicantQrController;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Card 6af82438, part 3: Settings QR feature (FINAS and CSDI only).
 *
 * "Generate a unique QR Code that if the applicant scans it, it will
 * automatically bring it to an Add Applicant page only."
 */
class ApplicantQrCodeTest extends TestCase
{
    use RefreshDatabase;

    private function agency(string $subdomain): Agency
    {
        return Agency::factory()->create(['subdomain' => $subdomain, 'name' => strtoupper($subdomain)]);
    }

    private function user(Agency $agency): User
    {
        return User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_the_settings_page_shows_a_qr_and_a_scan_link(): void
    {
        $agency = $this->agency('finas');

        $html = $this->actingAs($this->user($agency))
            ->get(route('settings.qr-code'))
            ->assertOk()
            ->getContent();

        // A real QR SVG was rendered, and the link carries a token.
        $this->assertStringContainsString('<svg', $html);
        $token = ApplicantQrController::tokenFor($agency->fresh());
        $this->assertStringContainsString($token, $html);
    }

    public function test_the_qr_settings_page_is_refused_on_other_tenants(): void
    {
        $agency = $this->agency('gulf');

        $this->actingAs($this->user($agency))
            ->get(route('settings.qr-code'))
            ->assertForbidden();
    }

    public function test_scanning_a_valid_token_opens_the_add_applicant_form(): void
    {
        $agency = $this->agency('finas');
        $token = ApplicantQrController::tokenFor($agency);

        // No login: a scan is anonymous.
        $this->get(route('applicant-qr.entry', ['token' => $token]))
            ->assertOk()
            ->assertSee('Add Applicant')
            ->assertSee('FINAS');
    }

    public function test_an_unknown_token_is_not_found(): void
    {
        $this->get(route('applicant-qr.entry', ['token' => 'not-a-real-token']))
            ->assertNotFound();
    }

    public function test_a_scan_creates_an_applicant_for_the_scanned_agency(): void
    {
        $agency = $this->agency('finas');
        $token = ApplicantQrController::tokenFor($agency);
        $position = Position::create(['name' => 'Nurse', 'category' => Position::CATEGORY_PROFESSIONAL]);

        $this->post(route('applicant-qr.store', ['token' => $token]), [
            'first_name' => 'Scanned',
            'last_name' => 'Applicant',
            'contact' => '09171234567',
            'position_id' => $position->id,
        ])->assertRedirect();

        $applicant = Applicant::withoutGlobalScopes()->where('last_name', 'Applicant')->first();

        $this->assertNotNull($applicant);
        $this->assertSame($agency->id, $applicant->agency_id);
        $this->assertSame(0, (int) $applicant->status_code, 'A scan must land in Pending');
        $this->assertSame(Applicant::TYPE_PROFESSIONAL, $applicant->applicant_type);
        $this->assertSame('QR Code', $applicant->source);
    }

    public function test_the_form_requires_a_first_and_last_name(): void
    {
        $agency = $this->agency('csdi');
        $token = ApplicantQrController::tokenFor($agency);

        $this->post(route('applicant-qr.store', ['token' => $token]), [])
            ->assertSessionHasErrors(['first_name', 'last_name']);
    }

    public function test_regenerating_invalidates_the_old_code(): void
    {
        $agency = $this->agency('finas');
        $old = ApplicantQrController::tokenFor($agency);

        $this->actingAs($this->user($agency))
            ->post(route('settings.qr-code.regenerate'))
            ->assertRedirect();

        $new = ApplicantQrController::tokenFor($agency->fresh());
        $this->assertNotSame($old, $new);

        $this->get(route('applicant-qr.entry', ['token' => $old]))->assertNotFound();
        $this->get(route('applicant-qr.entry', ['token' => $new]))->assertOk();
    }

    public function test_a_gulf_token_is_never_valid(): void
    {
        $agency = $this->agency('gulf');

        // Even if a token somehow existed for a tenant that does not offer the
        // feature, it must not open a form.
        $settings = is_array($agency->settings) ? $agency->settings : [];
        $settings[ApplicantQrController::SETTINGS_KEY] = 'gulptoken1234567890';
        $agency->update(['settings' => $settings]);

        $this->get(route('applicant-qr.entry', ['token' => 'gulptoken1234567890']))
            ->assertNotFound();
    }
}
