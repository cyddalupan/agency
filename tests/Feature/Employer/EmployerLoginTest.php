<?php

namespace Tests\Feature\Employer;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployerLoginTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Employer $employer;
    private User $employerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);
        $this->employerUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'user_type' => 'employer',
            'email' => 'employer@test.com',
            'password' => bcrypt('password'),
        ]);
    }

    #[Test]
    public function login_page_is_accessible(): void
    {
        $response = $this->get(route('employer.login'));

        $response->assertOk();
    }

    #[Test]
    public function employer_can_login_with_valid_credentials(): void
    {
        $response = $this->post(route('employer.login.post'), [
            'email'    => 'employer@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('employer.dashboard'));
        $this->assertAuthenticated();
    }

    #[Test]
    public function employer_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->post(route('employer.login.post'), [
            'email'    => 'employer@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function employer_can_logout(): void
    {
        $response = $this->actingAs($this->employerUser)
            ->post(route('employer.logout'));

        $response->assertRedirect(route('employer.login'));
        $this->assertGuest();
    }

    #[Test]
    public function unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('employer.dashboard'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function non_employer_user_is_redirected(): void
    {
        $nonEmployer = User::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => null,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($nonEmployer)
            ->get(route('employer.dashboard'));

        $response->assertRedirect(route('dashboard'));
    }

    #[Test]
    public function employer_dashboard_is_accessible(): void
    {
        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.dashboard'));

        $response->assertOk();
        $response->assertSee($this->employer->name);
    }
}
