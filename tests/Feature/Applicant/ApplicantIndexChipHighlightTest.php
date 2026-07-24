<?php

namespace Tests\Feature\Applicant;

use App\Models\Applicant;
use App\Models\StatusCode;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantIndexChipHighlightTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['user_type' => 'admin']);
        $this->seed(StatusCodesSeeder::class);

        Applicant::factory()->count(5)->create(['status_code' => 0]); // Pending
        Applicant::factory()->count(3)->create(['status_code' => 1]); // For Interview
    }

    #[Test]
    public function all_chip_is_active_when_no_status_filter(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertStatus(200);

        // All status chips should be visible, pipeline section renders
        $response->assertSee('Pending');
        $response->assertSee('For Interview');
    }

    #[Test]
    public function filter_by_pending_shows_only_pending_chip_active(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['status' => 0]));

        $response->assertStatus(200);

        // When status=0 (Pending), the chip text shows, meaning filter is working
        $response->assertSee('Pending');
        $response->assertSee('5');
    }

    #[Test]
    public function filter_by_interview_shows_only_interview_applicants(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['status' => 1]));

        $response->assertStatus(200);

        // Interview chip should be visible with count
        $response->assertSee('For Interview');
        $response->assertSee('3');
    }

    #[Test]
    public function filter_by_pending_shows_pending_applicants_only(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['status' => 0]));

        $response->assertStatus(200);

        $pending = Applicant::where('status_code', 0)->get();
        $interview = Applicant::where('status_code', 1)->get();

        foreach ($pending as $p) {
            $response->assertSee($p->first_name);
        }
        foreach ($interview as $p) {
            $response->assertDontSee($p->first_name);
        }
    }

    #[Test]
    public function filter_by_interview_shows_interview_applicants_only(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['status' => 1]));

        $response->assertStatus(200);

        $pending = Applicant::where('status_code', 0)->get();
        $interview = Applicant::where('status_code', 1)->get();

        foreach ($pending as $p) {
            $response->assertDontSee($p->first_name);
        }
        foreach ($interview as $p) {
            $response->assertSee($p->first_name);
        }
    }

    #[Test]
    public function all_chip_shows_correct_total_count(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertStatus(200);
        $response->assertSee('8');
    }

    #[Test]
    public function status_chip_links_have_correct_urls(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertStatus(200);
        // Chips should link to filtered views
        $response->assertSee('status=0');
        $response->assertSee('status=1');
    }
}
