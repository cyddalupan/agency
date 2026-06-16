<?php

namespace Tests\Feature\JobPosition;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobPositionShowTest extends TestCase
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
    public function unauthenticated_user_cannot_view_job_position(): void
    {
        $jobPosition = JobPosition::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => $this->employer->id,
        ]);

        $response = $this->get(route('employers.job-positions.show', [$this->employer, $jobPosition]));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function show_displays_job_position_details(): void
    {
        $jobPosition = JobPosition::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => $this->employer->id,
            'name'        => 'Registered Nurse',
            'salary'      => 60000,
            'total_slots' => 3,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('employers.job-positions.show', [$this->employer, $jobPosition]));

        $response->assertOk();
        $response->assertSee('Registered Nurse');
        $response->assertSee('60,000');
    }

    #[Test]
    public function show_has_back_link(): void
    {
        $jobPosition = JobPosition::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => $this->employer->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('employers.job-positions.show', [$this->employer, $jobPosition]));

        $response->assertSee('Back to Job Positions');
    }
}
