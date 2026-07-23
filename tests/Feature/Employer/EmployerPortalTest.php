<?php

namespace Tests\Feature\Employer;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployerPortalTest extends TestCase
{
    use RefreshDatabase;

    protected Agency $agency;
    protected Employer $employer;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();

        $this->employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $this->user = User::factory()->create([
            'email'       => 'employer@test.com',
            'password'    => bcrypt('password'),
            'user_type'   => 'employer',
            'agency_id'   => $this->agency->id,
            'employer_id' => $this->employer->id,
        ]);
    }

    protected function authenticate(): void
    {
        $this->actingAs($this->user);
    }

    // ------------------------------------------------
    //  Authentication Requirements
    // ------------------------------------------------

    #[Test]
    public function unauthenticated_users_are_redirected_to_login()
    {
        // Unauthenticated users hit the auth:web middleware which redirects
        // to the default 'login' named route (not employer.login).
        $this->get(route('employer.dashboard'))
            ->assertRedirectToRoute('login');

        $this->get(route('employer.job-positions.index'))
            ->assertRedirectToRoute('login');

        $this->get(route('employer.applicants'))
            ->assertRedirectToRoute('login');
    }

    #[Test]
    public function non_employer_users_are_redirected_to_employer_login()
    {
        $adminUser = User::factory()->create([
            'user_type' => 'admin',
            'agency_id' => $this->agency->id,
        ]);

        // The employer middleware redirects non-employer users to employer.login
        $this->actingAs($adminUser)
            ->get(route('employer.dashboard'))
            ->assertRedirect(route('employer.login'));

        $this->actingAs($adminUser)
            ->get(route('employer.job-positions.index'))
            ->assertRedirect(route('employer.login'));
    }

    // ------------------------------------------------
    //  Dashboard
    // ------------------------------------------------

    #[Test]
    public function dashboard_loads_successfully()
    {
        $this->authenticate();

        $this->get(route('employer.dashboard'))
            ->assertOk()
            ->assertSee($this->employer->name)
            ->assertSee('Welcome');
    }

    // ------------------------------------------------
    //  Job Positions
    // ------------------------------------------------

    #[Test]
    public function job_positions_index_page_loads()
    {
        $this->authenticate();

        $positions = JobPosition::factory()
            ->count(3)
            ->create([
                'employer_id' => $this->employer->id,
                'agency_id'   => $this->agency->id,
            ]);

        $this->get(route('employer.job-positions.index'))
            ->assertOk()
            ->assertSee($positions[0]->name)
            ->assertSee($positions[1]->name)
            ->assertSee($positions[2]->name);
    }

    #[Test]
    public function employer_only_sees_own_job_positions()
    {
        $this->authenticate();

        $ownPosition = JobPosition::factory()->create([
            'employer_id' => $this->employer->id,
            'agency_id'   => $this->agency->id,
        ]);

        $otherEmployer = Employer::factory()->create(['agency_id' => $this->agency->id]);
        $otherPosition = JobPosition::factory()->create([
            'employer_id' => $otherEmployer->id,
            'agency_id'   => $this->agency->id,
        ]);

        $this->get(route('employer.job-positions.index'))
            ->assertOk()
            ->assertSee($ownPosition->name)
            ->assertDontSee($otherPosition->name);
    }

    #[Test]
    public function job_position_creation_works()
    {
        $this->authenticate();

        $this->get(route('employer.job-positions.create'))
            ->assertOk();

        $this->post(route('employer.job-positions.store'), [
            'name'    => 'Software Engineer',
            'salary'  => 50000,
            'total_slots' => 3,
            'gender_preference' => 'any',
        ])->assertRedirect(route('employer.job-positions.index'));

        $this->assertDatabaseHas('job_positions', [
            'name'        => 'Software Engineer',
            'employer_id' => $this->employer->id,
            'agency_id'   => $this->agency->id,
        ]);
    }

    #[Test]
    public function job_position_edit_works()
    {
        $this->authenticate();

        $position = JobPosition::factory()->create([
            'employer_id' => $this->employer->id,
            'agency_id'   => $this->agency->id,
            'name'        => 'Original Name',
        ]);

        $this->get(route('employer.job-positions.edit', $position))
            ->assertOk()
            ->assertSee('Original Name');

        $this->put(route('employer.job-positions.update', $position), [
            'name'    => 'Updated Name',
            'salary'  => 60000,
        ])->assertRedirect(route('employer.job-positions.index'));

        $this->assertDatabaseHas('job_positions', [
            'id'   => $position->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function job_position_delete_works()
    {
        $this->authenticate();

        $position = JobPosition::factory()->create([
            'employer_id' => $this->employer->id,
            'agency_id'   => $this->agency->id,
        ]);

        $this->delete(route('employer.job-positions.destroy', $position))
            ->assertRedirect(route('employer.job-positions.index'));

        $this->assertDatabaseMissing('job_positions', ['id' => $position->id]);
    }

    #[Test]
    public function employer_cannot_edit_other_employers_job_position()
    {
        $this->authenticate();

        $otherEmployer = Employer::factory()->create(['agency_id' => $this->agency->id]);
        $otherPosition = JobPosition::factory()->create([
            'employer_id' => $otherEmployer->id,
            'agency_id'   => $this->agency->id,
        ]);

        $this->get(route('employer.job-positions.edit', $otherPosition))
            ->assertForbidden();

        $this->put(route('employer.job-positions.update', $otherPosition), [
            'name' => 'Hacked Name',
        ])->assertForbidden();

        $this->delete(route('employer.job-positions.destroy', $otherPosition))
            ->assertForbidden();
    }

    // ------------------------------------------------
    //  Applicants
    // ------------------------------------------------

    #[Test]
    public function applicants_page_loads()
    {
        $this->authenticate();

        $this->get(route('employer.applicants'))
            ->assertOk()
            ->assertSee('Applicants');
    }

    #[Test]
    public function applicants_page_shows_employers_applicants()
    {
        $this->authenticate();

        $position = JobPosition::factory()->create([
            'employer_id' => $this->employer->id,
            'agency_id'   => $this->agency->id,
        ]);

        $applicant = Applicant::factory()->create([
            'employer_id' => $this->employer->id,
            'agency_id'   => $this->agency->id,
            'job_id'      => $position->id,
            'first_name'  => 'Juan',
            'last_name'   => 'Dela Cruz',
            'status'      => 'pending',
        ]);

        $this->get(route('employer.applicants'))
            ->assertOk()
            ->assertSee('Juan')
            ->assertSee('Dela Cruz');
    }

    #[Test]
    public function applicants_page_does_not_show_other_employers_applicants()
    {
        $this->authenticate();

        $otherEmployer = Employer::factory()->create(['agency_id' => $this->agency->id]);
        $otherPosition = JobPosition::factory()->create([
            'employer_id' => $otherEmployer->id,
            'agency_id'   => $this->agency->id,
        ]);

        Applicant::factory()->create([
            'employer_id' => $otherEmployer->id,
            'agency_id'   => $this->agency->id,
            'job_id'      => $otherPosition->id,
            'first_name'  => 'Secret',
            'last_name'   => 'Applicant',
        ]);

        $this->get(route('employer.applicants'))
            ->assertOk()
            ->assertDontSee('Secret');
    }

    // ------------------------------------------------
    //  Billing
    // ------------------------------------------------

    #[Test]
    public function billing_page_loads()
    {
        $this->authenticate();

        $this->get(route('employer.billing.index'))
            ->assertOk()
            ->assertSee('Billing');
    }

    #[Test]
    public function soa_page_loads()
    {
        $this->authenticate();

        $this->get(route('employer.billing.soa'))
            ->assertOk();
    }
}
