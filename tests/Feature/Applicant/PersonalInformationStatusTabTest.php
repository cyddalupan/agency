<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Employer;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS "Personal Information" — PI: 6. Status tab (TDD).
 *
 * The Status tab must let an admin set, per applicant:
 *  - Applicant# (optional)
 *  - Applicant Status: FRA/Employer (dropdown listing the agency's FRA — the
 *    employers table, same as the edit page), Status (dropdown), Status Date (date)
 *  - Repat Status: Repat (tick box), Repat Date (date)
 *  - Save Status (button) persisting all of the above.
 */
class PersonalInformationStatusTabTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Applicant $applicant;
    private Employer $employer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);
        $this->seed(\Database\Seeders\StatusTransitionSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);
        $this->employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Al-Muftah Recruitment',
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    private function getShowHtml(): string
    {
        return $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();
    }

    #[Test]
    public function status_tab_renders_all_pi6_fields(): void
    {
        $html = $this->getShowHtml();

        // Status tab panel + button
        $this->assertStringContainsString('data-pi-panel="status"', $html);
        $this->assertStringContainsString('Save Status', $html);

        // Applicant# (optional)
        $this->assertStringContainsString('Applicant#', $html);
        $this->assertStringContainsString('name="applicant_no"', $html);

        // Applicant Status: FRA/Employer dropdown — lists the agency's FRA
        // (the employers table), same as the edit page's employer dropdown.
        $this->assertStringContainsString('FRA/Employer', $html);
        $this->assertStringContainsString('name="employer_id"', $html);
        $this->assertStringContainsString($this->employer->name, $html);
        $this->assertStringNotContainsString('name="fra"', $html);
        $this->assertStringNotContainsString('For FRA', $html);

        // Status dropdown (status_code) seeded options visible
        $this->assertStringContainsString('name="status_code"', $html);
        $this->assertStringContainsString('Pending', $html);
        $this->assertStringContainsString('Deployed', $html);

        // Status Date
        $this->assertStringContainsString('name="status_date"', $html);

        // Repat checkbox + Repat Date were removed (2026-08-10): status 35
        // Repatriated in the dropdown covers it — the repat boolean was never
        // read anywhere. Repatriated must be reachable via the dropdown instead.
        $this->assertStringNotContainsString('name="repat"', $html);
        $this->assertStringNotContainsString('name="repat_date"', $html);
        $this->assertMatchesRegularExpression('/value="35"[^>]*>\s*Repatriated\s*<\/option>/', $html);

        // Form posts to the status route
        $this->assertStringContainsString(route('applicants.status', $this->applicant), $html);
    }

    #[Test]
    public function save_status_persists_all_pi6_fields(): void
    {
        $response = $this->actingAs($this->user)->patch(
            route('applicants.status', $this->applicant),
            [
                'applicant_no' => 'LN-2026-0042',
                'employer_id'  => $this->employer->id,
                'status_code'  => 1, // For Interview (valid from Pending)
                'status_date'  => '2026-08-01',
            ]
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('applicants', [
            'id'           => $this->applicant->id,
            'applicant_no' => 'LN-2026-0042',
            'employer_id'  => $this->employer->id,
            'status_code'  => 1,
            'status_date'  => '2026-08-01 00:00:00',
        ]);
    }

    #[Test]
    public function applicant_number_is_optional(): void
    {
        $response = $this->actingAs($this->user)->patch(
            route('applicants.status', $this->applicant),
            [
                'status_code' => 0,
            ]
        );

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'id'          => $this->applicant->id,
            'status_code' => 0,
        ]);
    }

    #[Test]
    public function employer_dropdown_rejects_invalid_employer(): void
    {
        $response = $this->actingAs($this->user)->patch(
            route('applicants.status', $this->applicant),
            [
                'status_code' => 0,
                'employer_id' => 999999,
            ]
        );

        $response->assertSessionHasErrors('employer_id');
    }

    #[Test]
    public function edit_page_labels_employer_dropdown_as_fra_employer(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('applicants.edit', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('FRA/Employer', $html);
        $this->assertStringContainsString('name="employer_id"', $html);
        $this->assertStringContainsString($this->employer->name, $html);
    }

    #[Test]
    public function repat_field_is_ignored_after_checkbox_removal(): void
    {
        // The repat tick box was removed (2026-08-10) — status 35 Repatriated
        // in the dropdown replaces it. A stale repat payload must not write
        // the obsolete flag.
        $this->actingAs($this->user)->patch(
            route('applicants.status', $this->applicant),
            ['status_code' => 0, 'repat' => '1', 'repat_date' => '2026-08-03']
        );
        $this->assertDatabaseHas('applicants', [
            'id'          => $this->applicant->id,
            'repat'       => 0,
            'repat_date'  => null,
        ]);
    }
}
