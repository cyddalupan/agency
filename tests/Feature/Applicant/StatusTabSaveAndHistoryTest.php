<?php

namespace Tests\Feature\Applicant;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS Status tab (Cyd report 2026-08-09).
 *
 * 1. The Status tab must save ANY valid status change — it must NOT be
 *    blocked by pipeline transition rules (an applicant stuck on a status
 *    with no transitions, e.g. 51 For Passporting, could never save).
 * 2. Every status change must record a status_changed history entry.
 * 3. Status changes made via the Edit Applicant form must also record a
 *    status_changed history entry.
 */
class StatusTabSaveAndHistoryTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'name'      => 'Encoder Cyd',
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    private function applicantWithStatus(int $statusCode): Applicant
    {
        return Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => $statusCode,
        ]);
    }

    #[Test]
    public function status_tab_saves_change_from_status_without_transitions(): void
    {
        // Status 51 (For Passporting) has NO rows in status_transitions.
        // Previously any save from here failed with a transition error.
        $applicant = $this->applicantWithStatus(51);

        $response = $this->actingAs($this->user)
            ->patch(route('applicants.status', $applicant), [
                'status_code' => 1, // For Interview
                'status_date' => '2026-08-09',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertSame(1, $applicant->fresh()->status_code);
    }

    #[Test]
    public function status_tab_allows_skipping_pipeline_steps(): void
    {
        $applicant = $this->applicantWithStatus(0); // Pending

        $response = $this->actingAs($this->user)
            ->patch(route('applicants.status', $applicant), [
                'status_code' => 6, // Selected — skip steps
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(6, $applicant->fresh()->status_code);
    }

    #[Test]
    public function status_tab_records_history_for_every_status_change(): void
    {
        $applicant = $this->applicantWithStatus(51);

        $this->actingAs($this->user)
            ->patch(route('applicants.status', $applicant), [
                'status_code' => 2,
            ]);

        $log = ActivityLog::where('subject_type', Applicant::class)
            ->where('subject_id', $applicant->id)
            ->where('action', 'status_changed')
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertSame($this->user->id, $log->user_id);
        $this->assertSame(51, $log->metadata['old_status']);
        $this->assertSame(2, $log->metadata['new_status']);
    }

    #[Test]
    public function edit_applicant_status_change_records_status_history(): void
    {
        $applicant = $this->applicantWithStatus(0);

        $response = $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), [
                'first_name'  => $applicant->first_name,
                'last_name'   => $applicant->last_name,
                'status_code' => 8, // Deployed
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertSame(8, $applicant->fresh()->status_code);

        $log = ActivityLog::where('subject_type', Applicant::class)
            ->where('subject_id', $applicant->id)
            ->where('action', 'status_changed')
            ->latest()
            ->first();

        $this->assertNotNull($log, 'Edit-form status change must be logged');
        $this->assertSame($this->user->id, $log->user_id);
        $this->assertSame(0, $log->metadata['old_status']);
        $this->assertSame(8, $log->metadata['new_status']);
    }

    #[Test]
    public function edit_applicant_without_status_change_does_not_duplicate_history(): void
    {
        $applicant = $this->applicantWithStatus(3);

        $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), [
                'first_name'  => $applicant->first_name,
                'last_name'   => $applicant->last_name,
                'status_code' => 3, // unchanged
            ]);

        $this->assertSame(3, $applicant->fresh()->status_code);

        $count = ActivityLog::where('subject_type', Applicant::class)
            ->where('subject_id', $applicant->id)
            ->where('action', 'status_changed')
            ->count();

        $this->assertSame(0, $count, 'No history entry when status did not change');
    }

    #[Test]
    public function status_history_section_shows_empty_state(): void
    {
        $applicant = $this->applicantWithStatus(0);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Status History', $html);
        $this->assertStringContainsString('No status changes yet', $html);
    }
}
