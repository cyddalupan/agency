<?php

namespace Tests\Feature\Applicant;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Country;
use App\Models\Employer;
use App\Models\StatusCode;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS Status tab — Status History table format (Cyd spec 2026-08-09).
 *
 * The history table must show these columns:
 *   created | status (colored tab) | sub status | agency/employer |
 *   country | remarks | handled by | status date
 */
class StatusHistoryTableFormatTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create(['name' => 'Gulf Horizon International Services Inc.']);
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'name'      => 'Encoder Cyd',
        ]);

        $employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Al-Muftah Recruitment',
        ]);
        $country = Country::factory()->create(['name' => 'Saudi Arabia']);

        $this->applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'employer_id'  => $employer->id,
            'country_id'   => $country->id,
            'fra'          => 'for_fra',
            'remarks'      => 'Priority deployment',
            'status_date'  => '2026-08-09',
            'status_code'  => 17,
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    private function addHistoryEntry(int $newStatus): ActivityLog
    {
        return ActivityLog::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'subject_type' => Applicant::class,
            'subject_id'   => $this->applicant->id,
            'action'       => 'status_changed',
            'description'  => "Encoder Cyd changed applicant status from 16 to {$newStatus}.",
            'metadata'     => ['old_status' => 16, 'new_status' => $newStatus],
        ]);
    }

    #[Test]
    public function status_history_table_renders_all_expected_columns(): void
    {
        $this->addHistoryEntry(17);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        foreach (['Created', 'Status', 'Sub Status', 'Agency/Employer', 'Country', 'Remarks', 'Handled By', 'Status Date'] as $header) {
            $this->assertStringContainsString($header, $html, "Missing header: {$header}");
        }
    }

    #[Test]
    public function status_history_row_shows_status_as_colored_badge(): void
    {
        $this->addHistoryEntry(17);

        $color = StatusCode::where('code', 17)->first()->color;
        $label = StatusCode::where('code', 17)->first()->label;

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString($label, $html);
        $this->assertStringContainsString($color, $html, 'Status badge must use the status color');
    }

    #[Test]
    public function status_history_row_shows_context_values(): void
    {
        $this->addHistoryEntry(17);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        // Sub status (FRA label)
        $this->assertStringContainsString('For FRA', $html);
        // Agency / employer
        $this->assertStringContainsString('Gulf Horizon International Services Inc.', $html);
        $this->assertStringContainsString('Al-Muftah Recruitment', $html);
        // Country
        $this->assertStringContainsString('Saudi Arabia', $html);
        // Remarks
        $this->assertStringContainsString('Priority deployment', $html);
        // Handled by
        $this->assertStringContainsString('Encoder Cyd', $html);
        // Status date
        $this->assertStringContainsString('2026-08-09', $html);
    }

    #[Test]
    public function status_history_empty_state_still_renders(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Status History', $html);
        $this->assertStringContainsString('No status changes yet', $html);
    }
}
