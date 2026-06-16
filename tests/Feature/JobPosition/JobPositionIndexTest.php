<?php

namespace Tests\Feature\JobPosition;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobPositionIndexTest extends TestCase
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
    public function unauthenticated_user_cannot_access_job_positions(): void
    {
        $response = $this->get(route('employers.job-positions.index', $this->employer));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function index_shows_job_positions_for_employer(): void
    {
        JobPosition::factory()->count(3)->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => $this->employer->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('employers.job-positions.index', $this->employer));

        $response->assertOk();
        $response->assertSee('Job Positions');
    }

    #[Test]
    public function index_only_shows_positions_for_the_current_employer(): void
    {
        $otherEmployer = Employer::factory()->create(['agency_id' => $this->agency->id]);

        $pos1 = JobPosition::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => $this->employer->id,
            'name'        => 'Position A',
        ]);
        JobPosition::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => $otherEmployer->id,
            'name'        => 'Position B',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('employers.job-positions.index', $this->employer));

        $response->assertSee('Position A');
        $response->assertDontSee('Position B');
    }
}
