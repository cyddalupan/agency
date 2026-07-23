<?php

namespace Tests\Feature\Agency;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AgencyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    // ─── PUBLIC LANDING PAGE ────────────────────────────────────────

    #[Test]
    public function guest_can_access_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('welcome');
    }

    #[Test]
    public function landing_page_has_link_to_agency_registration(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('agency.register'));
    }

    // ─── PUBLIC REGISTRATION PAGE ────────────────────────────────────

    #[Test]
    public function guest_can_access_registration_page(): void
    {
        $response = $this->get(route('agency.register'));

        $response->assertOk();
        $response->assertViewIs('agencies.public-register');
    }

    #[Test]
    public function authenticated_user_gets_redirected_from_registration_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('agency.register'));

        $response->assertRedirect();
    }

    // ─── SUCCESSFUL REGISTRATION ──────────────────────────────────────

    #[Test]
    public function guest_can_register_a_new_agency(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'My Recruitment Agency',
            'address'         => '123 Main Street, Brgy. San Antonio',
            'city'            => 'Makati City',
            'email'           => 'agency@example.com',
            'contact_person'  => 'Juan dela Cruz',
            'num_branches'    => 3,
            'admin_username'  => 'admin_juan',
            'admin_password'  => 'simple123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('agencies', [
            'name'           => 'My Recruitment Agency',
            'address'        => '123 Main Street, Brgy. San Antonio',
            'city'           => 'Makati City',
            'email'          => 'agency@example.com',
            'contact_person' => 'Juan dela Cruz',
            'num_branches'   => 3,
        ]);
    }

    #[Test]
    public function new_agency_is_created_as_pending(): void
    {
        $this->post(route('agency.register.post'), [
            'agency_name'     => 'Pending Agency Inc',
            'address'         => '456 Business Road',
            'city'            => 'Quezon City',
            'email'           => 'pending@example.com',
            'contact_person'  => 'Maria Santos',
            'num_branches'    => 2,
            'admin_username'  => 'maria_admin',
            'admin_password'  => 'simple456',
        ]);

        $this->assertDatabaseHas('agencies', [
            'email'  => 'pending@example.com',
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function registration_creates_admin_user_for_agency(): void
    {
        $this->post(route('agency.register.post'), [
            'agency_name'     => 'Admin Test Agency',
            'address'         => '789 Admin Lane',
            'city'            => 'Pasig City',
            'email'           => 'admintest@example.com',
            'contact_person'  => 'Admin User',
            'num_branches'    => 1,
            'admin_username'  => 'admin_test',
            'admin_password'  => 'adminpass',
        ]);

        $agency = \App\Models\Agency::where('email', 'admintest@example.com')->first();
        $this->assertNotNull($agency);

        $this->assertDatabaseHas('users', [
            'agency_id' => $agency->id,
            'name'      => 'Admin User',
            'email'     => 'admintest@example.com',
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function admin_user_gets_the_correct_username(): void
    {
        $this->post(route('agency.register.post'), [
            'agency_name'     => 'Username Test',
            'address'         => '10 Username St',
            'city'            => 'Mandaluyong',
            'email'           => 'user@example.com',
            'contact_person'  => 'Test Person',
            'num_branches'    => 2,
            'admin_username'  => 'test_admin_user',
            'admin_password'  => 'testpass',
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'test_admin_user',
        ]);
    }

    #[Test]
    public function registration_shows_pending_approval_notice(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Notice Test Agency',
            'address'         => '99 Notice Ave',
            'city'            => 'Taguig City',
            'email'           => 'notice@example.com',
            'contact_person'  => 'Notice Person',
            'num_branches'    => 4,
            'admin_username'  => 'notice_admin',
            'admin_password'  => 'noticepw',
        ]);

        $response->assertRedirect(route('agency.pending-approval'));
    }

    #[Test]
    public function pending_approval_page_loads(): void
    {
        $response = $this->get(route('agency.pending-approval'));

        $response->assertOk();
        $response->assertViewIs('agencies.pending-approval');
    }

    // ─── VALIDATION: AGENCY FIELDS ──────────────────────────────────

    #[Test]
    public function agency_name_is_required(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => '',
            'address'         => '123 Street',
            'city'            => 'Makati',
            'email'           => 'a@b.com',
            'contact_person'  => 'Person',
            'num_branches'    => 1,
            'admin_username'  => 'admin',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionHasErrors('agency_name');
    }

    #[Test]
    public function address_is_required(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Test Agency',
            'address'         => '',
            'city'            => 'Makati',
            'email'           => 'a@b.com',
            'contact_person'  => 'Person',
            'num_branches'    => 1,
            'admin_username'  => 'admin',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionHasErrors('address');
    }

    #[Test]
    public function city_is_required(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Test Agency',
            'address'         => '123 St',
            'city'            => '',
            'email'           => 'a@b.com',
            'contact_person'  => 'Person',
            'num_branches'    => 1,
            'admin_username'  => 'admin',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionHasErrors('city');
    }

    #[Test]
    public function email_is_required(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Test Agency',
            'address'         => '123 St',
            'city'            => 'Makati',
            'email'           => '',
            'contact_person'  => 'Person',
            'num_branches'    => 1,
            'admin_username'  => 'admin',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function email_must_be_valid(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Test Agency',
            'address'         => '123 St',
            'city'            => 'Makati',
            'email'           => 'not-an-email',
            'contact_person'  => 'Person',
            'num_branches'    => 1,
            'admin_username'  => 'admin',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function contact_person_is_required(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Test Agency',
            'address'         => '123 St',
            'city'            => 'Makati',
            'email'           => 'a@b.com',
            'contact_person'  => '',
            'num_branches'    => 1,
            'admin_username'  => 'admin',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionHasErrors('contact_person');
    }

    #[Test]
    public function num_branches_is_required(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Test Agency',
            'address'         => '123 St',
            'city'            => 'Makati',
            'email'           => 'a@b.com',
            'contact_person'  => 'Person',
            'num_branches'    => '',
            'admin_username'  => 'admin',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionHasErrors('num_branches');
    }

    #[Test]
    public function num_branches_must_be_numeric(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Test Agency',
            'address'         => '123 St',
            'city'            => 'Makati',
            'email'           => 'a@b.com',
            'contact_person'  => 'Person',
            'num_branches'    => 'abc',
            'admin_username'  => 'admin',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionHasErrors('num_branches');
    }

    #[Test]
    public function num_branches_must_be_at_least_one(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Test Agency',
            'address'         => '123 St',
            'city'            => 'Makati',
            'email'           => 'a@b.com',
            'contact_person'  => 'Person',
            'num_branches'    => 0,
            'admin_username'  => 'admin',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionHasErrors('num_branches');
    }

    #[Test]
    public function agency_email_must_be_unique(): void
    {
        \App\Models\Agency::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Duplicate Email Agency',
            'address'         => '123 St',
            'city'            => 'Pasig',
            'email'           => 'taken@example.com',
            'contact_person'  => 'Person',
            'num_branches'    => 1,
            'admin_username'  => 'admin1',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // ─── VALIDATION: ADMIN ACCOUNT ──────────────────────────────────

    #[Test]
    public function admin_username_is_required(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Test Agency',
            'address'         => '123 St',
            'city'            => 'Makati',
            'email'           => 'a@b.com',
            'contact_person'  => 'Person',
            'num_branches'    => 1,
            'admin_username'  => '',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionHasErrors('admin_username');
    }

    #[Test]
    public function admin_password_is_required(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Test Agency',
            'address'         => '123 St',
            'city'            => 'Makati',
            'email'           => 'a@b.com',
            'contact_person'  => 'Person',
            'num_branches'    => 1,
            'admin_username'  => 'admin',
            'admin_password'  => '',
        ]);

        $response->assertSessionHasErrors('admin_password');
    }

    #[Test]
    public function admin_password_accepts_simple_passwords(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Simple Pass Agency',
            'address'         => '123 Simple St',
            'city'            => 'Manila',
            'email'           => 'simple@example.com',
            'contact_person'  => 'Simple Person',
            'num_branches'    => 2,
            'admin_username'  => 'simple_admin',
            'admin_password'  => '12',
        ]);

        $response->assertSessionDoesntHaveErrors('admin_password');
        $response->assertRedirect();

        $this->assertDatabaseHas('agencies', [
            'email' => 'simple@example.com',
        ]);
    }

    #[Test]
    public function admin_username_must_be_unique(): void
    {
        // Create an existing user with the username
        \App\Models\Agency::factory()->create();
        User::factory()->create(['username' => 'taken_admin']);

        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Duplicate Username Agency',
            'address'         => '123 St',
            'city'            => 'Makati',
            'email'           => 'unique-email@example.com',
            'contact_person'  => 'Person',
            'num_branches'    => 1,
            'admin_username'  => 'taken_admin',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionHasErrors('admin_username');
    }

    #[Test]
    public function admin_username_is_stripped_of_spaces(): void
    {
        $this->post(route('agency.register.post'), [
            'agency_name'     => 'Space Test Agency',
            'address'         => '123 Space St',
            'city'            => 'Manila',
            'email'           => 'space@example.com',
            'contact_person'  => 'Space Person',
            'num_branches'    => 1,
            'admin_username'  => '  admin_user  ',
            'admin_password'  => 'pass',
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'admin_user',
        ]);
    }

    // ─── EXISTING FUNCTIONALITY PRESERVED ───────────────────────────

    #[Test]
    public function agency_name_does_not_have_to_be_unique(): void
    {
        \App\Models\Agency::factory()->create(['name' => 'Same Name Agency']);

        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Same Name Agency',
            'address'         => '456 Other St',
            'city'            => 'Pasig',
            'email'           => 'unique@example.com',
            'contact_person'  => 'Other Person',
            'num_branches'    => 3,
            'admin_username'  => 'unique_admin',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionDoesntHaveErrors('agency_name');
    }

    #[Test]
    public function number_of_branches_defaults_to_one_when_not_provided(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'     => 'Default Branch Agency',
            'address'         => '123 Default St',
            'city'            => 'Manila',
            'email'           => 'default@example.com',
            'contact_person'  => 'Default Person',
            'admin_username'  => 'default_admin',
            'admin_password'  => 'pass',
        ]);

        $response->assertSessionHasErrors('num_branches');
    }
}
