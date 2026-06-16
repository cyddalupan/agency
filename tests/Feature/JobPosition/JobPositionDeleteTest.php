<?php

namespace Tests\Feature\JobPosition;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobPositionDeleteTest extends TestCase
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
    public function unauthenticated_user_cannot_delete(): void
    {
        $jobPosition = JobPosition::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => $this->employer->id,
        ]);

        $response = $this->delete(route('employers.job-positions.destroy', [$this->employer, $jobPosition]));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_delete_job_position(): void
    {
        $jobPosition = JobPosition::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => $this->employer->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('employers.job-positions.destroy', [$this->employer, $jobPosition]));

        $response->assertRedirect(route('employers.job-positions.index', $this->employer));
        $this->assertDatabaseMissing('job_positions', ['id' => $jobPosition->id]);
    }
}
