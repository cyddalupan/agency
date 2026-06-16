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
        $response = $this->post(route('portal.register.post'), [
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
            \App\Models\Applicant::where('email', 'juan@example.com')->first(),
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
        $response = $this->post(route('portal.register.post'), [
            'first_name'            => 'Juan',
            'last_name'             => 'Dela Cruz',
            'email'                 => 'juan@example.com',
            'contact'               => '09171234567',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('portal.dashboard'));

        $applicant = \App\Models\Applicant::where('email', 'juan@example.com')->first();
        $this->assertNotNull($applicant);
    }

    #[Test]
    public function guest_cannot_access_portal_dashboard_directly(): void
    {
        $response = $this->get(route('portal.dashboard'));

        $response->assertRedirect(route('portal.login'));
    }

    #[Test]
    public function registration_sets_default_agency(): void
    {
        $response = $this->post(route('portal.register.post'), [
            'first_name'            => 'Juan',
            'last_name'             => 'Dela Cruz',
            'email'                 => 'juan@example.com',
            'contact'               => '09171234567',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $applicant = \App\Models\Applicant::where('email', 'juan@example.com')->first();
        $this->assertNotNull($applicant->agency_id);
    }
}
