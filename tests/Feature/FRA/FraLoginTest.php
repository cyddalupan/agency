<?php

namespace Tests\Feature\FRA;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FraLoginTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Employer $employer;
    private User $employerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create([
            'status' => 'active',
        ]);

        $this->employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $this->employerUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'username' => 'testemployer',
            'email' => 'employer@test.com',
            'password' => bcrypt('password123'),
            'user_type' => 'employer',
            'status' => 'active',
        ]);
    }

    #[Test]
    public function fra_login_page_is_accessible(): void
    {
        $response = $this->get(route('fra.login'));

        $response->assertStatus(200);
        $response->assertSee(__('messages.sign_in'));
    }

    #[Test]
    public function fra_login_page_has_username_field(): void
    {
        $response = $this->get(route('fra.login'));

        $response->assertSee(__('messages.email_or_username'));
    }

    #[Test]
    public function fra_can_login_with_valid_username_and_password(): void
    {
        $response = $this->post(route('fra.login.post'), [
            'login' => 'testemployer',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('fra.dashboard'));
        $this->assertAuthenticated();
    }

    #[Test]
    public function fra_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->post(route('fra.login.post'), [
            'login' => 'testemployer',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    #[Test]
    public function fra_cannot_login_with_non_employer_user(): void
    {
        User::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => null,
            'username' => 'staffuser',
            'password' => bcrypt('password123'),
            'user_type' => 'staff',
            'status' => 'active',
        ]);

        $response = $this->post(route('fra.login.post'), [
            'login' => 'staffuser',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    #[Test]
    public function fra_cannot_login_with_inactive_user(): void
    {
        User::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'username' => 'inactiveemp',
            'password' => bcrypt('password123'),
            'user_type' => 'employer',
            'status' => 'inactive',
        ]);

        $response = $this->post(route('fra.login.post'), [
            'login' => 'inactiveemp',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    #[Test]
    public function fra_can_logout(): void
    {
        $this->actingAs($this->employerUser);

        $response = $this->post(route('fra.logout'));

        $response->assertRedirect(route('fra.login'));
        $this->assertGuest();
    }

    #[Test]
    public function unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('fra.dashboard'));

        $response->assertRedirect(route('fra.login'));
    }

    #[Test]
    public function fra_dashboard_is_accessible_when_authenticated(): void
    {
        $this->actingAs($this->employerUser);

        $response = $this->get(route('fra.dashboard'));

        $response->assertStatus(200);
    }

    #[Test]
    public function remember_me_checkbox_is_present(): void
    {
        $response = $this->get(route('fra.login'));

        $response->assertSee(__('messages.remember_me'));
    }

    #[Test]
    public function fra_login_has_forgot_password_link(): void
    {
        $response = $this->get(route('fra.login'));

        $response->assertSee('Forgot');
    }
}
