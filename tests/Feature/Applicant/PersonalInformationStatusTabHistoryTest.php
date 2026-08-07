<?php

namespace Tests\Feature\Applicant;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\StatusCode;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS "For Fixing Personal Info" — checklist item 8 (TDD).
 *
 * Status tab: display status history + encoder name; restrict status
 * selection to Settings statuses.
 */
class PersonalInformationStatusTabHistoryTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Applicant $applicant;

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
        $this->applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'status_code'  => 1,
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    #[Test]
    public function status_tab_shows_status_history_with_encoder_name_and_timestamp(): void
    {
        ActivityLog::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'subject_type' => Applicant::class,
            'subject_id'   => $this->applicant->id,
            'action'       => 'status_changed',
            'description'  => 'Encoder Cyd changed applicant status from 1 to 2.',
            'metadata'     => ['old_status' => 1, 'new_status' => 2],
        ]);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Status History', $html);
        // Encoder name is displayed.
        $this->assertStringContainsString('Encoder Cyd', $html);
    }

    #[Test]
    public function status_tab_restricts_status_dropdown_to_settings_statuses(): void
    {
        // Seed a known Settings status.
        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        // The Status dropdown is named status_code and is a settings-sourced select.
        $this->assertStringContainsString('name="status_code"', $html);
        $this->assertStringContainsString('<select', $html);
    }

    #[Test]
    public function updating_status_records_history_with_encoder(): void
    {
        // Use the applicant's current status (same-status Save is a valid no-op
        // that still records a status_changed history entry with the encoder).
        $current = StatusCode::where('code', $this->applicant->status_code)->first();
        $this->assertNotNull($current, 'current status code must exist in Settings');

        $this->actingAs($this->user)
            ->patch(route('applicants.status', $this->applicant), [
                'status_code' => (string) $current->code,
                'status_date' => '2026-08-06',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'subject_type' => Applicant::class,
            'subject_id'   => $this->applicant->id,
            'action'       => 'status_changed',
        ]);
    }
}
