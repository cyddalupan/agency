<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Database\Seeders\StatusTransitionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);
        $this->seed(StatusTransitionSeeder::class);

        $this->agency = Agency::factory()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->user = User::factory()->create(['agency_id' => $this->agency->id]);
        $this->applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'status_code' => 0, // Pending
        ]);
    }

    #[Test]
    public function guest_cannot_update_status(): void
    {
        $response = $this->patch(route('applicants.status', $this->applicant), [
            'status_code' => 1,
        ]);

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function can_update_to_valid_next_status(): void
    {
        $response = $this->actingAs($this->user)
            ->patch(route('applicants.status', $this->applicant), [
                'status_code' => 1, // For Interview
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(1, $this->applicant->fresh()->status_code);
    }

    #[Test]
    public function can_skip_pipeline_steps_from_status_tab(): void
    {
        // Status tab must allow moving to ANY status — pipeline transition
        // rules no longer block it (Cyd report 2026-08-09).
        $response = $this->actingAs($this->user)
            ->patch(route('applicants.status', $this->applicant), [
                'status_code' => 6, // Selected — skip steps
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(6, $this->applicant->fresh()->status_code);
    }

    #[Test]
    public function can_transition_to_terminal_status(): void
    {
        $response = $this->actingAs($this->user)
            ->patch(route('applicants.status', $this->applicant), [
                'status_code' => 38, // Cancel
            ]);

        $response->assertRedirect();
        $this->assertEquals(38, $this->applicant->fresh()->status_code);
    }

    #[Test]
    public function status_code_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->patch(route('applicants.status', $this->applicant), [
                'status_code' => '',
            ]);

        $response->assertSessionHasErrors('status_code');
    }

    #[Test]
    public function status_code_must_exist(): void
    {
        $response = $this->actingAs($this->user)
            ->patch(route('applicants.status', $this->applicant), [
                'status_code' => 999,
            ]);

        $response->assertSessionHasErrors('status_code');
    }

    #[Test]
    public function follows_full_pipeline(): void
    {
        $this->actingAs($this->user);

        // Step through the pipeline
        $response = $this->patch(route('applicants.status', $this->applicant), [
            'status_code' => 1,
        ]);
        $response->assertRedirect();
        $this->assertEquals(1, $this->applicant->fresh()->status_code);

        $response = $this->patch(route('applicants.status', $this->applicant), [
            'status_code' => 2,
        ]);
        $response->assertRedirect();
        $this->assertEquals(2, $this->applicant->fresh()->status_code);
    }
}
