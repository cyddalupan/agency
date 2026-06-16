<?php

namespace Tests\Feature\Portal;

use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantPortalTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_page_is_accessible(): void
    {
        $response = $this->get(route('portal.login'));

        $response->assertOk();
    }

    #[Test]
    public function unauthenticated_applicant_is_redirected_to_login(): void
    {
        $response = $this->get(route('portal.dashboard'));

        $response->assertRedirect(route('portal.login'));
    }

    #[Test]
    public function unauthenticated_applicant_is_redirected_to_login_for_profile(): void
    {
        $response = $this->get(route('portal.profile'));

        $response->assertRedirect(route('portal.login'));
    }

    #[Test]
    public function applicant_can_login_with_valid_credentials(): void
    {
        $applicant = Applicant::factory()->create([
            'email'    => 'applicant@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('portal.login.post'), [
            'email'    => 'applicant@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($applicant, 'applicant');
    }

    #[Test]
    public function applicant_cannot_login_with_invalid_credentials(): void
    {
        Applicant::factory()->create([
            'email'    => 'applicant@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('portal.login.post'), [
            'email'    => 'applicant@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('applicant');
    }

    #[Test]
    public function applicant_dashboard_shows_applicant_information(): void
    {
        $applicant = Applicant::factory()->create([
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'email'      => 'juan@example.com',
        ]);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertSee('Juan');
        $response->assertSee('Dela Cruz');
    }

    #[Test]
    public function applicant_can_view_profile(): void
    {
        $applicant = Applicant::factory()->create([
            'first_name' => 'Maria',
            'last_name'  => 'Santos',
            'email'      => 'maria@example.com',
            'contact'    => '09171234567',
        ]);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Maria');
        $response->assertSee('Santos');
        $response->assertSee('maria@example.com');
        $response->assertSee('09171234567');
    }

    #[Test]
    public function authenticated_applicant_can_logout(): void
    {
        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($applicant, 'applicant')
            ->post(route('portal.logout'));

        $response->assertRedirect(route('portal.login'));
        $this->assertGuest('applicant');
    }

    #[Test]
    public function already_authenticated_applicant_sees_dashboard_on_login_page(): void
    {
        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.login'));

        $response->assertRedirect(route('portal.dashboard'));
    }

    #[Test]
    public function applicant_cannot_access_admin_routes(): void
    {
        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}
