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
 * LANDAS Status tab — long status labels must not break the badge design
 * (Cyd report 2026-08-09).
 *
 * The Status History badge must use the same no-wrap pattern as the
 * applicants index page (badge badge-sm whitespace-nowrap) so long labels
 * like "For OWWA Make-Up Class" render on one line instead of wrapping
 * inside the fixed-height badge and clipping.
 */
class StatusHistoryLongLabelBadgeTest extends TestCase
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

    #[Test]
    public function status_history_badge_uses_nowrap_and_sm_classes(): void
    {
        // Create a long status label to reproduce the clipping bug.
        $status = StatusCode::firstOrCreate(
            ['code' => 999],
            ['label' => 'For OWWA Make-Up Class', 'color' => '#f97316', 'sort_order' => 999]
        );

        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => $status->code,
        ]);

        ActivityLog::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'subject_type' => Applicant::class,
            'subject_id'   => $applicant->id,
            'action'       => 'status_changed',
            'description'  => "Encoder Cyd changed applicant status to {$status->code}.",
            'metadata'     => ['old_status' => 1, 'new_status' => $status->code],
        ]);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant))
            ->assertOk()
            ->getContent();

        // The badge must use the index-page no-wrap pattern (single line).
        $this->assertStringContainsString('whitespace-nowrap', $html, 'History badge must prevent wrapping');
        $this->assertStringContainsString('badge badge-sm', $html, 'History badge must use badge-sm sizing');
        // And the full long label must still be present.
        $this->assertStringContainsString('For OWWA Make-Up Class', $html);
    }
}
