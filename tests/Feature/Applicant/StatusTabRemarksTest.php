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
 * LANDAS Status tab — remarks input on the status change form
 * (Cyd request 2026-08-09).
 *
 * The Status tab form must include a remarks field, and the remarks must be
 * persisted on the applicant and snapshotted into the status_changed history
 * entry so it shows in the Status History "Remarks" column.
 */
class StatusTabRemarksTest extends TestCase
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
    public function status_tab_form_includes_remarks_input(): void
    {
        $applicant = $this->applicantWithStatus(51);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="remarks"', $html, 'Status form must have a remarks input');
        $this->assertStringContainsString('Remarks', $html);
    }

    #[Test]
    public function saving_status_with_remarks_persists_and_snapshots_remarks(): void
    {
        $applicant = $this->applicantWithStatus(51);

        $this->actingAs($this->user)
            ->patch(route('applicants.status', $applicant), [
                'status_code' => 1,
                'remarks'     => 'Medical clearance follow-up needed',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $fresh = $applicant->fresh();
        $this->assertSame(1, $fresh->status_code);
        $this->assertSame('Medical clearance follow-up needed', $fresh->remarks);

        $log = ActivityLog::where('subject_type', Applicant::class)
            ->where('subject_id', $applicant->id)
            ->where('action', 'status_changed')
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('Medical clearance follow-up needed', $log->metadata['remarks']);
    }

    #[Test]
    public function saving_status_without_remarks_clears_remarks_field(): void
    {
        $applicant = $this->applicantWithStatus(51);
        $applicant->update(['remarks' => 'Old remark']);

        $this->actingAs($this->user)
            ->patch(route('applicants.status', $applicant), [
                'status_code' => 2,
                'remarks'     => '',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($applicant->fresh()->remarks);
    }
}
