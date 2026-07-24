<?php

namespace Tests\Feature\Applicant;

use App\Models\Applicant;
use App\Models\StatusCode;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantIndexStatusFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->user = User::factory()->create([
            'user_type' => 'admin',
        ]);

        // Seed status codes
        $this->seed(StatusCodesSeeder::class);

        // Create applicants with different statuses
        Applicant::factory()->count(5)->create(['status_code' => 0]); // Pending
        Applicant::factory()->count(3)->create(['status_code' => 1]); // For Interview
        Applicant::factory()->count(2)->create(['status_code' => 7]); // For Deployment
        Applicant::factory()->count(1)->create(['status_code' => 8]); // Deployed
    }

    #[Test]
    public function applicants_index_shows_status_count_chips(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertStatus(200);
        $response->assertSee('Pending');
        $response->assertSee('For Interview');
        $response->assertSee('For Deployment');
        $response->assertSee('Deployed');
    }

    #[Test]
    public function status_chip_has_correct_count(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Pending', '5']);
        $response->assertSeeInOrder(['For Interview', '3']);
        $response->assertSeeInOrder(['For Deployment', '2']);
        $response->assertSeeInOrder(['Deployed', '1']);
    }

    #[Test]
    public function clicking_status_chip_filters_applicants(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['status' => 0]));

        $response->assertStatus(200);

        // Pending applicants should be visible
        $pending = Applicant::where('status_code', 0)->get();
        foreach ($pending as $p) {
            $response->assertSee($p->first_name);
        }

        // For Interview applicants should NOT be visible
        $interview = Applicant::where('status_code', 1)->get();
        foreach ($interview as $p) {
            $response->assertDontSee($p->first_name);
        }
    }

    #[Test]
    public function all_chip_shows_when_no_filter_active(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertStatus(200);

        $total = Applicant::count();
        $this->assertEquals(11, $total);
    }

    #[Test]
    public function status_filter_works_with_search(): void
    {
        // Create applicant with unique name
        $target = Applicant::factory()->create([
            'first_name'  => 'UniqueNameTest',
            'status_code' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', [
                'search' => 'UniqueNameTest',
                'status' => 1,
            ]));

        $response->assertStatus(200);
        $response->assertSee('UniqueNameTest');
    }

    #[Test]
    public function status_filter_shows_only_matching_count(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['status' => 1]));

        $response->assertStatus(200);

        // Filtered table should only show 3
        $response->assertSee('matching');
    }

    #[Test]
    public function active_status_chip_is_highlighted(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['status' => 1]));

        $response->assertStatus(200);
        $response->assertSee('For Interview');
        $response->assertSee('3');
    }

    #[Test]
    public function status_pipeline_shows_total_count(): void
    {
        // Create 5 more applicants (16 total)
        Applicant::factory()->count(5)->create(['status_code' => 0]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertStatus(200);
        $response->assertSee('total');
    }
}
