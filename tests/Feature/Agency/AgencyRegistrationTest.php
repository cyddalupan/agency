<?php

namespace Tests\Feature\Agency;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AgencyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    // ─── PUBLIC REGISTRATION PAGE ────────────────────────────────────

    #[Test]
    public function guest_can_access_public_registration_page(): void
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

        $response->assertRedirect(); // Redirect to dashboard
    }

    // ─── SUCCESSFUL REGISTRATION ──────────────────────────────────────

    #[Test]
    public function guest_can_register_a_new_agency(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'        => 'My Recruitment Agency',
            'subdomain'          => 'myagency',
            'admin_name'         => 'John Admin',
            'admin_email'        => 'john@example.com',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert agency was created
        $this->assertDatabaseHas('agencies', [
            'name'      => 'My Recruitment Agency',
            'subdomain' => 'myagency',
        ]);
    }

    #[Test]
    public function new_agency_is_created_as_pending(): void
    {
        $this->post(route('agency.register.post'), [
            'agency_name'        => 'Pending Agency Inc',
            'subdomain'          => 'pendingagency',
            'admin_name'         => 'Jane Admin',
            'admin_email'        => 'jane@example.com',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $this->assertDatabaseHas('agencies', [
            'subdomain' => 'pendingagency',
            'status'    => 'pending',
        ]);
    }

    #[Test]
    public function registration_creates_admin_user_for_agency(): void
    {
        $this->post(route('agency.register.post'), [
            'agency_name'        => 'Admin Test Agency',
            'subdomain'          => 'admintest',
            'admin_name'         => 'Admin User',
            'admin_email'        => 'adminuser@example.com',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $agency = \App\Models\Agency::where('subdomain', 'admintest')->first();
        $this->assertNotNull($agency);

        $this->assertDatabaseHas('users', [
            'agency_id' => $agency->id,
            'name'      => 'Admin User',
            'email'     => 'adminuser@example.com',
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function registration_shows_pending_approval_notice(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'        => 'Notice Test Agency',
            'subdomain'          => 'noticetest',
            'admin_name'         => 'Notice User',
            'admin_email'        => 'notice@example.com',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
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

    // ─── VALIDATION ──────────────────────────────────────────────────

    #[Test]
    public function agency_name_is_required(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'        => '',
            'subdomain'          => 'validname',
            'admin_name'         => 'Admin',
            'admin_email'        => 'admin@example.com',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('agency_name');
    }

    #[Test]
    public function subdomain_is_required(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'        => 'Some Agency',
            'subdomain'          => '',
            'admin_name'         => 'Admin',
            'admin_email'        => 'admin@example.com',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('subdomain');
    }

    #[Test]
    public function subdomain_must_have_valid_format(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'        => 'Bad Subdomain',
            'subdomain'          => 'INVALID_SUBDOMAIN!!!',
            'admin_name'         => 'Admin',
            'admin_email'        => 'admin@example.com',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('subdomain');
    }

    #[Test]
    public function subdomain_must_be_unique(): void
    {
        // Create an existing agency with a subdomain
        \App\Models\Agency::factory()->create(['subdomain' => 'taken']);

        $response = $this->post(route('agency.register.post'), [
            'agency_name'        => 'Duplicate Subdomain Agency',
            'subdomain'          => 'taken',
            'admin_name'         => 'Admin',
            'admin_email'        => 'admin@example.com',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('subdomain');
    }

    #[Test]
    public function subdomain_is_auto_lowercased(): void
    {
        $this->post(route('agency.register.post'), [
            'agency_name'        => 'Case Test Agency',
            'subdomain'          => 'UpperCaseSubdomain',
            'admin_name'         => 'Admin',
            'admin_email'        => 'case@example.com',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $this->assertDatabaseHas('agencies', [
            'subdomain' => 'uppercasesubdomain',
        ]);
    }

    #[Test]
    public function admin_email_is_required(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'        => 'No Email Agency',
            'subdomain'          => 'noemail',
            'admin_name'         => 'Admin',
            'admin_email'        => '',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('admin_email');
    }

    #[Test]
    public function admin_email_must_be_valid(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'        => 'Bad Email',
            'subdomain'          => 'bademail',
            'admin_name'         => 'Admin',
            'admin_email'        => 'not-an-email',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('admin_email');
    }

    #[Test]
    public function admin_password_is_required(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'        => 'No Pass',
            'subdomain'          => 'nopass',
            'admin_name'         => 'Admin',
            'admin_email'        => 'admin@example.com',
            'admin_password'     => '',
            'admin_password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('admin_password');
    }

    #[Test]
    public function admin_password_must_be_confirmed(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'        => 'No Confirm',
            'subdomain'          => 'noconfirm',
            'admin_name'         => 'Admin',
            'admin_email'        => 'admin@example.com',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'DifferentPass1!',
        ]);

        $response->assertSessionHasErrors('admin_password');
    }

    #[Test]
    public function admin_password_must_meet_minimum_length(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'        => 'Short Pass',
            'subdomain'          => 'shortpass',
            'admin_name'         => 'Admin',
            'admin_email'        => 'admin@example.com',
            'admin_password'     => 'Ab1!',
            'admin_password_confirmation' => 'Ab1!',
        ]);

        $response->assertSessionHasErrors('admin_password');
    }

    #[Test]
    public function admin_name_is_required(): void
    {
        $response = $this->post(route('agency.register.post'), [
            'agency_name'        => 'No Admin Name',
            'subdomain'          => 'noadminname',
            'admin_name'         => '',
            'admin_email'        => 'admin@example.com',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('admin_name');
    }

    // ─── AGENCY NAME UNIQUENESS ──────────────────────────────────────

    #[Test]
    public function agency_name_does_not_have_to_be_unique(): void
    {
        \App\Models\Agency::factory()->create(['name' => 'Same Name Agency']);

        $response = $this->post(route('agency.register.post'), [
            'agency_name'        => 'Same Name Agency',
            'subdomain'          => 'different-subdomain',
            'admin_name'         => 'Admin',
            'admin_email'        => 'unique@example.com',
            'admin_password'     => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionDoesntHaveErrors('agency_name');
    }
}
