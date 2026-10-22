<?php

namespace Tests\Feature\Auth;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * FRA / sponsor accounts must not land on the agency panel after signing in
 * through the agency login form. ModuleAccess denies every agency module to
 * those user types, so the redirect produced a bare 403
 * ("Your account does not have access to this module.") for a *successful*
 * login — Josh's report on the card "FINAS: FRA Feature" (2026-10-22).
 */
class PortalLoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Employer $employer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['status' => 'active']);
        $this->employer = Employer::factory()->create(['agency_id' => $this->agency->id]);
    }

    private function makeUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'agency_id' => $this->agency->id,
            'password'  => bcrypt('secret-pass'),
            'status'    => 'active',
            'user_type' => 'employer',
        ], $attributes));
    }

    #[Test]
    public function fra_account_signing_in_on_the_agency_form_lands_on_the_fra_dashboard(): void
    {
        $user = $this->makeUser([
            'email'       => 'fra@test.com',
            'username'    => 'fra',
            'employer_id' => $this->employer->id,
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'fra@test.com',
            'password' => 'secret-pass',
        ]);

        $response->assertRedirect(route('fra.dashboard'));
        $this->assertAuthenticatedAs($user);

        // The destination must actually work — no 403 for a valid FRA login.
        $this->get(route('fra.dashboard'))->assertOk();
    }

    #[Test]
    public function fra_account_can_sign_in_with_its_username_on_the_agency_form(): void
    {
        $this->makeUser([
            'email'       => 'fra2@test.com',
            'username'    => 'FRA',
            'employer_id' => $this->employer->id,
        ]);

        $this->post(route('login'), [
            'email'    => 'FRA',
            'password' => 'secret-pass',
        ])->assertRedirect(route('fra.dashboard'));

        $this->assertAuthenticated();
    }

    #[Test]
    public function stored_intended_agency_url_does_not_bounce_a_fra_account_into_a_403(): void
    {
        $this->makeUser([
            'email'       => 'fra3@test.com',
            'username'    => 'fra3',
            'employer_id' => $this->employer->id,
        ]);

        // Simulate the FRA user hitting an agency page while logged out.
        $this->get(route('agency.dashboard'))->assertRedirect(route('login'));

        $this->post(route('login'), [
            'email'    => 'fra3@test.com',
            'password' => 'secret-pass',
        ])->assertRedirect(route('fra.dashboard'));
    }

    #[Test]
    public function sponsor_account_signing_in_on_the_agency_form_lands_on_the_sponsor_dashboard(): void
    {
        $this->makeUser([
            'email'     => 'sponsor@test.com',
            'username'  => 'sponsor',
            'user_type' => 'sponsor',
        ]);

        $this->post(route('login'), [
            'email'    => 'sponsor@test.com',
            'password' => 'secret-pass',
        ])->assertRedirect(route('sponsor.dashboard'));

        $this->assertAuthenticated();
    }

    #[Test]
    public function agency_staff_still_land_on_the_agency_dashboard(): void
    {
        $this->makeUser([
            'email'     => 'admin@test.com',
            'username'  => 'admin',
            'user_type' => 'admin',
        ]);

        $this->post(route('login'), [
            'email'    => 'admin@test.com',
            'password' => 'secret-pass',
        ])->assertRedirect(route('agency.dashboard'));

        $this->assertAuthenticated();
    }
}
