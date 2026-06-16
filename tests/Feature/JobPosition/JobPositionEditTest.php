<?php

namespace Tests\Feature\JobPosition;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobPositionEditTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Employer $employer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_edit_form(): void
    {
        $jobPosition = JobPosition::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => $this->employer->id,
        ]);

        $response = $this->get(route('employers.job-positions.edit', [$this->employer, $jobPosition]));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function edit_form_displays_correctly(): void
    {
        $jobPosition = JobPosition::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => $this->employer->id,
            'name'        => 'Civil Engineer',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('employers.job-positions.edit', [$this->employer, $jobPosition]));

        $response->assertOk();
        $response->assertSee('Edit Job Position');
        $response->assertSee('Civil Engineer');
    }

    #[Test]
    public function update_changes_job_position(): void
    {
        $jobPosition = JobPosition::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => $this->employer->id,
            'name'        => 'Old Title',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('employers.job-positions.update', [$this->employer, $jobPosition]), [
                'name'   => 'New Title',
                'salary' => 75000,
            ]);

        $response->assertRedirect(route('employers.job-positions.index', $this->employer));
        $this->assertDatabaseHas('job_positions', [
            'id'     => $jobPosition->id,
            'name'   => 'New Title',
            'salary' => 75000,
        ]);
    }

    #[Test]
    public function update_preserves_agency_id(): void
    {
        $jobPosition = JobPosition::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => $this->employer->id,
        ]);

        $this->actingAs($this->user)
            ->put(route('employers.job-positions.update', [$this->employer, $jobPosition]), [
                'name' => 'Updated',
            ]);

        $this->assertDatabaseHas('job_positions', [
            'id'        => $jobPosition->id,
            'agency_id' => $this->agency->id,
        ]);
    }
}
