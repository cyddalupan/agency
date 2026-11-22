<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\JobPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b008b8dc9020955b2cc16e1, defect A regression test.
 *
 * The applicant portal authenticates the SEPARATE `applicant` guard. When
 * IdentifyAgency only asked `auth()->check()` (the default `web` guard), a
 * logged-in applicant looked like a guest: no tenant was bound, TenantScope
 * failed closed, and every portal page 500'd with UnboundTenantException.
 *
 * These tests log in through the guard the portal actually uses, on the
 * control-plane host (the shape QA reproduced), and assert both that the page
 * renders and that the bound tenant is the applicant's own agency.
 */
class ApplicantGuardTenantBindingTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Agency $otherAgency;

    protected function setUp(): void
    {
        parent::setUp();

        // Name the agencies so the "first()" order is the WRONG one: if any
        // code path still guesses, it picks $otherAgency and the test notices.
        $this->otherAgency = Agency::factory()->create(['name' => 'AAA Other', 'subdomain' => 'aaa-other']);
        $this->agency = Agency::factory()->create(['name' => 'Zeta Agency', 'subdomain' => 'zeta-agency']);
    }

    private function signedInApplicant(): Applicant
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'email'     => 'portal@example.com',
            'password'  => bcrypt('password'),
        ]);

        $this->assertGuest('web');
        $this->actingAs($applicant, 'applicant');
        // actingAs() also calls Auth::shouldUse('applicant'), so auth()->check()
        // would follow the applicant guard. Point the default back at `web` so
        // the precondition matches the real request: the web guard is a guest
        // while the applicant guard is signed in. That mismatch is exactly what
        // made IdentifyAgency bind nothing.
        $this->app['auth']->shouldUse('web');
        $this->assertFalse(auth()->check(), 'web guard must still be a guest for this test to mean anything');
        $this->assertTrue(auth('applicant')->check());

        return $applicant;
    }

    #[Test]
    public function applicant_guard_user_gets_their_tenant_bound_on_dashboard(): void
    {
        $applicant = $this->signedInApplicant();

        $response = $this->get('/portal/dashboard');

        $response->assertOk();
        $response->assertSee($applicant->first_name);

        $bound = app()->has('tenant_agency') ? app('tenant_agency') : null;
        $this->assertNotNull($bound, 'no tenant was bound for a logged-in applicant');
        $this->assertSame($this->agency->id, $bound->id,
            'bound tenant ' . $bound->id . ' is not the applicant\'s agency ' . $this->agency->id);
    }

    #[Test]
    public function applicant_tenant_scopes_queries_to_the_applicants_agency(): void
    {
        $applicant = $this->signedInApplicant();

        // Two open jobs: one in the applicant's agency, one in the agency that
        // sorts first. The portal must show only the applicant's own.
        JobPosition::factory()->for($this->agency)->create(['status' => 'open', 'name' => 'Own Agency Job']);
        JobPosition::factory()->for($this->otherAgency)->create(['status' => 'open', 'name' => 'Other Agency Job']);

        $response = $this->get('/portal/jobs');

        $response->assertOk();
        $response->assertSee('Own Agency Job');
        $response->assertDontSee('Other Agency Job');

        $this->assertSame(1, JobPosition::where('status', 'open')->count());
        $this->assertSame($this->agency->id, app('tenant_agency')->id);
    }

    #[Test]
    public function applicant_can_open_profile(): void
    {
        $this->signedInApplicant();

        $this->get('/portal/profile')->assertOk();
        $this->assertSame($this->agency->id, app('tenant_agency')->id);
    }

    /**
     * The guest direction of the same middleware: an unauthenticated portal
     * page must redirect to the login page, not 500 before login ever happens.
     */
    #[Test]
    public function guest_portal_pages_redirect_instead_of_throwing(): void
    {
        foreach (['/portal/dashboard', '/portal/profile', '/portal/jobs'] as $path) {
            $this->get($path)->assertRedirect(route('portal.login'));
        }
    }

    /**
     * A registration on the agency's own host signs the applicant in through
     * the applicant guard and lands on a working dashboard — the exact flow QA
     * reproduced as a 500 after the registration 302.
     */
    #[Test]
    public function registration_on_a_tenant_host_lands_on_a_working_dashboard(): void
    {
        $response = $this->post(
            'https://' . $this->agency->subdomain . '.fixitautoservices.com'.route('portal.register.post', [], false),
            [
                'first_name'            => 'Maria',
                'last_name'             => 'Santos',
                'email'                 => 'maria@example.com',
                'contact'               => '09171234567',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]
        );

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticated('applicant');

        // The redirect target is built from the app URL (the control plane).
        // Follow it there with the applicant guard session in place.
        $this->get('/portal/dashboard')
            ->assertOk()
            ->assertSee('Maria');

        $this->assertSame($this->agency->id, app('tenant_agency')->id);
    }
}
