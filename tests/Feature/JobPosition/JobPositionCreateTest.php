<?php

namespace Tests\Feature\JobPosition;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobPositionCreateTest extends TestCase
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
    public function unauthenticated_user_cannot_access_create_form(): void
    {
        $response = $this->get(route('employers.job-positions.create', $this->employer));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function create_form_displays_correctly(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('employers.job-positions.create', $this->employer));

        $response->assertOk();
        $response->assertSee('Add Job Position');
    }

    #[Test]
    public function store_creates_job_position(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('employers.job-positions.store', $this->employer), [
                'name'             => 'Software Engineer',
                'gender_preference' => 'any',
                'salary'           => 50000,
                'total_slots'      => 5,
            ]);

        $response->assertRedirect(route('employers.job-positions.index', $this->employer));
        $this->assertDatabaseHas('job_positions', [
            'name'        => 'Software Engineer',
            'employer_id' => $this->employer->id,
            'status'      => 'open',
        ]);
    }

    #[Test]
    public function store_uses_authenticated_user_agency_id(): void
    {
        $this->actingAs($this->user)
            ->post(route('employers.job-positions.store', $this->employer), [
                'name' => 'Nurse',
            ]);

        $this->assertDatabaseHas('job_positions', [
            'name'       => 'Nurse',
            'agency_id'  => $this->agency->id,
        ]);
    }
}
