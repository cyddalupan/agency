<?php

namespace Tests\Feature\Portal;

use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    /**
     * Post to the registration form AS A TENANT HOST.
     *
     * Registration has no tenant on the control plane any more (defect B,
     * card 6b008b8dc9020955b2cc16e1): a self-registration belongs to the agency
     * whose site it arrives on, and the control plane no longer guesses
     * Agency::first(). Rejecting it is covered separately below. The portal is
     * normally reached on the agency's own host, so these tests do the same.
     */
    private function postRegistration(array $payload)
    {
        return $this->post('https://' . $this->agency->subdomain . '.fixitautoservices.com' . route('portal.register.post', [], false), $payload);
    }

    /**
     * Post-registration assertions read the applicant row directly, with no
     * HTTP request and no bound tenant. TenantScope fails closed on web
     * requests (card 6b008b8dc9020955b2cc16e1), so say the read is deliberate.
     */
    private function applicantByEmail(string $email): ?\App\Models\Applicant
    {
        return \App\Support\Tenant::bypass(
            fn () => \App\Models\Applicant::where('email', $email)->first(),
            'ApplicantRegistrationTest: read back the applicant just registered'
        );
    }

    #[Test]
    public function registration_page_displays(): void
    {
        $response = $this->get(route('portal.register'));

        $response->assertOk();
        $response->assertSee('Register');
        $response->assertSee('First Name');
        $response->assertSee('Email');
        $response->assertSee('Password');
    }

    #[Test]
    public function applicant_can_register(): void
    {
        $response = $this->postRegistration([
            'first_name'      => 'Juan',
            'last_name'       => 'Dela Cruz',
            'email'           => 'juan@example.com',
            'contact'         => '09171234567',
            'password'        => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('portal.dashboard'));

        $this->assertDatabaseHas('applicants', [
            'email'      => 'juan@example.com',
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
        ]);

        $this->assertAuthenticatedAs(
            $this->applicantByEmail('juan@example.com'),
            'applicant'
        );
    }

    #[Test]
    public function registration_requires_valid_data(): void
    {
        $response = $this->post(route('portal.register.post'), []);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'password']);
    }

    #[Test]
    public function registration_requires_valid_email(): void
    {
        $response = $this->post(route('portal.register.post'), [
            'first_name'      => 'Juan',
            'last_name'       => 'Dela Cruz',
            'email'           => 'not-an-email',
            'password'        => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function registration_requires_unique_email(): void
    {
        \App\Models\Applicant::factory()->create([
            'email'     => 'existing@example.com',
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->post(route('portal.register.post'), [
            'first_name'      => 'Juan',
            'last_name'       => 'Dela Cruz',
            'email'           => 'existing@example.com',
            'contact'         => '09171234567',
            'password'        => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function password_must_be_confirmed(): void
    {
        $response = $this->post(route('portal.register.post'), [
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'email'      => 'juan@example.com',
            'password'   => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function registration_assigns_pending_status(): void
    {
        $response = $this->postRegistration([
            'first_name'            => 'Juan',
            'last_name'             => 'Dela Cruz',
            'email'                 => 'juan@example.com',
            'contact'               => '09171234567',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('portal.dashboard'));

        $applicant = $this->applicantByEmail('juan@example.com');
        $this->assertNotNull($applicant);
    }

    /**
     * Defect B (card 6b008b8dc9020955b2cc16e1): QA saw a control-plane
     * self-registration land in agency_id=1 because the controller guessed
     * Agency::first(). A registration that arrives with no tenant is refused —
     * and, crucially, NOT written into anybody's agency.
     */
    #[Test]
    public function control_plane_registration_is_rejected_and_creates_nothing(): void
    {
        // A second agency sorting before $this->agency would be the old
        // "first()" target; the assertion below is that neither gets the row.
        Agency::factory()->create(['name' => 'AAA First', 'subdomain' => 'aaa-first']);

        $response = $this->post(route('portal.register.post'), [
            'first_name'            => 'Juan',
            'last_name'             => 'Dela Cruz',
            'email'                 => 'control@example.com',
            'contact'               => '09171234567',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('registration');
        $response->assertRedirect();

        $this->assertSame(0, \App\Support\Tenant::bypass(
            fn () => \App\Models\Applicant::withoutGlobalScopes()->where('email', 'control@example.com')->count(),
            'ApplicantRegistrationTest: assert the rejected registration wrote no row'
        ));

        $this->assertGuest('applicant');
    }

    #[Test]
    public function guest_cannot_access_portal_dashboard_directly(): void
    {
        $response = $this->get(route('portal.dashboard'));

        $response->assertRedirect(route('portal.login'));
    }

    /**
     * Replaces the old "registration_sets_default_agency": there is no default
     * agency any more. On the agency's own host the registrant lands in THAT
     * agency, not in whichever one sorts first.
     */
    #[Test]
    public function registration_assigns_the_agency_of_the_host(): void
    {
        $first = Agency::factory()->create(['name' => 'AAA First', 'subdomain' => 'aaa-first']);
        $this->assertNotSame($first->id, $this->agency->id);

        $response = $this->postRegistration([
            'first_name'            => 'Juan',
            'last_name'             => 'Dela Cruz',
            'email'                 => 'juan@example.com',
            'contact'               => '09171234567',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('portal.dashboard'));

        $applicant = $this->applicantByEmail('juan@example.com');
        $this->assertSame($this->agency->id, $applicant->agency_id,
            'registrant was written into ' . $applicant->agency_id . ', expected host agency ' . $this->agency->id);
    }
}
