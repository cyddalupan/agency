<?php

namespace Tests\Feature\Sponsor;

use App\Models\Agency;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SponsorRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create([
            'status' => 'active',
        ]);
    }

    #[Test]
    public function sponsor_registration_page_is_accessible(): void
    {
        $response = $this->get(route('sponsor.register'));

        $response->assertOk();
        $response->assertSee('Register');
    }

    #[Test]
    public function sponsor_can_register(): void
    {
        $response = $this->post(route('sponsor.register.post'), [
            'id_number'      => 'ID-123456',
            'company_name'   => 'ACME Corp',
            'email'          => 'sponsor@acme.com',
            'contact_no'     => '+639123456789',
            'viber'          => '+639123456789',
            'address'        => '123 Business Ave',
            'city'           => 'Manila',
            'password'       => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('sponsor.dashboard'));

        $this->assertDatabaseHas('sponsors', [
            'id_number'    => 'ID-123456',
            'company_name' => 'ACME Corp',
            'email'        => 'sponsor@acme.com',
            'contact_no'   => '+639123456789',
            'viber'        => '+639123456789',
            'address'      => '123 Business Ave',
            'city'         => 'Manila',
        ]);

        $this->assertDatabaseHas('users', [
            'email'    => 'sponsor@acme.com',
            'user_type' => 'sponsor',
        ]);
    }

    #[Test]
    public function id_number_is_required_for_registration(): void
    {
        $response = $this->post(route('sponsor.register.post'), [
            'company_name' => 'ACME Corp',
            'email'        => 'sponsor@acme.com',
            'password'     => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('id_number');
    }

    #[Test]
    public function id_number_must_be_unique(): void
    {
        Sponsor::create([
            'agency_id'    => $this->agency->id,
            'id_number'    => 'ID-123456',
            'company_name' => 'Existing Corp',
            'email'        => 'existing@acme.com',
            'contact_no'   => '+639123456789',
            'status'       => 'pending',
        ]);

        $response = $this->post(route('sponsor.register.post'), [
            'id_number'    => 'ID-123456',
            'company_name' => 'ACME Corp',
            'email'        => 'sponsor@acme.com',
            'password'     => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('id_number');
    }

    #[Test]
    public function email_must_be_unique_in_users_table(): void
    {
        User::factory()->create([
            'email' => 'existing@user.com',
        ]);

        $response = $this->post(route('sponsor.register.post'), [
            'id_number'    => 'ID-123456',
            'company_name' => 'ACME Corp',
            'email'        => 'existing@user.com',
            'password'     => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function sponsor_is_redirected_to_login_when_not_authenticated(): void
    {
        $response = $this->get(route('sponsor.dashboard'));

        $response->assertRedirect(route('sponsor.login'));
    }

    #[Test]
    public function logged_in_sponsor_is_redirected_to_dashboard_from_register(): void
    {
        $user = User::create([
            'agency_id' => $this->agency->id,
            'name'      => 'Test Sponsor',
            'email'     => 'test@sponsor.com',
            'username'  => 'SPONSOR-TEST',
            'password'  => bcrypt('password'),
            'user_type' => 'sponsor',
            'status'    => 'active',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('sponsor.register'));
        $response->assertRedirect(route('sponsor.dashboard'));

        $response = $this->get(route('sponsor.login'));
        $response->assertRedirect(route('sponsor.dashboard'));
    }

    #[Test]
    public function non_sponsor_logged_in_user_is_redirected_away_from_sponsor_guest_routes(): void
    {
        $user = User::create([
            'agency_id' => $this->agency->id,
            'name'      => 'Admin User',
            'email'     => 'admin@test.com',
            'username'  => 'ADMIN',
            'password'  => bcrypt('password'),
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('sponsor.register'));
        $response->assertRedirect();
        $this->assertNotEquals(route('sponsor.dashboard'), $response->getTargetUrl());
    }

    #[Test]
    public function sponsor_can_view_login_page(): void
    {
        $response = $this->get(route('sponsor.login'));

        $response->assertOk();
    }

    #[Test]
    public function sponsor_can_login_with_id_number(): void
    {
        Sponsor::create([
            'agency_id'    => $this->agency->id,
            'id_number'    => 'ID-123456',
            'company_name' => 'ACME Corp',
            'email'        => 'sponsor@acme.com',
            'contact_no'   => '+639123456789',
            'status'       => 'active',
        ]);

        User::create([
            'agency_id' => $this->agency->id,
            'name'      => 'ACME Corp',
            'email'     => 'sponsor@acme.com',
            'username'  => 'ID-123456',
            'password'  => bcrypt('password123'),
            'user_type' => 'sponsor',
            'status'    => 'active',
        ]);

        $response = $this->post(route('sponsor.login.post'), [
            'login'    => 'ID-123456',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('sponsor.dashboard'));
        $this->assertAuthenticated();
    }

    #[Test]
    public function sponsor_can_logout(): void
    {
        Sponsor::create([
            'agency_id'    => $this->agency->id,
            'id_number'    => 'ID-123456',
            'company_name' => 'ACME Corp',
            'email'        => 'sponsor@acme.com',
            'contact_no'   => '+639123456789',
            'status'       => 'active',
        ]);

        $user = User::create([
            'agency_id' => $this->agency->id,
            'name'      => 'ACME Corp',
            'email'     => 'sponsor@acme.com',
            'username'  => 'ID-123456',
            'password'  => bcrypt('password123'),
            'user_type' => 'sponsor',
            'status'    => 'active',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('sponsor.logout'));

        $response->assertRedirect(route('sponsor.login'));
        $this->assertGuest();
    }

    #[Test]
    public function sponsor_sees_lineup_on_dashboard(): void
    {
        $user = User::create([
            'agency_id' => $this->agency->id,
            'name'      => 'ACME Corp',
            'email'     => 'sponsor@test.com',
            'username'  => 'SPONSOR-001',
            'password'  => bcrypt('password'),
            'user_type' => 'sponsor',
            'status'    => 'active',
        ]);

        Sponsor::create([
            'agency_id'    => $this->agency->id,
            'id_number'    => 'SPONSOR-001',
            'company_name' => 'ACME Corp',
            'email'        => 'sponsor@test.com',
            'status'       => 'active',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('sponsor.dashboard'));

        $response->assertOk();
        $response->assertSee('Line Up');
    }

    #[Test]
    public function sponsor_dashboard_shows_my_applicants_tab(): void
    {
        $user = User::create([
            'agency_id' => $this->agency->id,
            'name'      => 'ACME Corp',
            'email'     => 'sponsor@test.com',
            'username'  => 'SPONSOR-001',
            'password'  => bcrypt('password'),
            'user_type' => 'sponsor',
            'status'    => 'active',
        ]);

        Sponsor::create([
            'agency_id'    => $this->agency->id,
            'id_number'    => 'SPONSOR-001',
            'company_name' => 'ACME Corp',
            'email'        => 'sponsor@test.com',
            'status'       => 'active',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('sponsor.my-applicants'));

        $response->assertOk();
        $response->assertSee('My Applicants');
    }
}
