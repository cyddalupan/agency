<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Employer;
use App\Models\StatusCode;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * "LANDAS PI: 8 item 3" — the Status tab's FRA dropdown lists the agency's FRA
 * (the employers table — FRA portal users are employer-type), identical to the
 * edit page's employer dropdown. Both are labeled "FRA/Employer" (Toybits
 * report 2026-08-15: the old static No FRA / For FRA / FRA Completed options
 * were wrong). Status dropdown options still come from the status_codes table.
 */
class AgencyStatusOptionsTest extends TestCase
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
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    private function applicant(): Applicant
    {
        return Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'status_code' => 0,
        ]);
    }

    private function statusTabHtml(Applicant $a): string
    {
        return $this->actingAs($this->user)
            ->get(route('applicants.show', $a))
            ->assertOk()
            ->getContent();
    }

    // ---- FRA/Employer dropdown (Status tab) ----

    #[Test]
    public function status_tab_lists_agency_employers_in_fra_dropdown(): void
    {
        Employer::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Al-Muftah Recruitment',
        ]);
        $a = $this->applicant();

        $html = $this->statusTabHtml($a);

        $this->assertStringContainsString('FRA/Employer', $html);
        $this->assertStringContainsString('name="employer_id"', $html);
        $this->assertStringContainsString('Al-Muftah Recruitment', $html);
        // The old static FRA stage options are gone from the Status tab.
        $this->assertStringNotContainsString('name="fra"', $html);
        $this->assertStringNotContainsString('For FRA', $html);
        $this->assertStringNotContainsString('FRA Completed', $html);
    }

    #[Test]
    public function status_tab_lists_only_same_agency_employers(): void
    {
        Employer::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Gulf Employer One',
        ]);
        $otherAgency = Agency::factory()->create();
        Employer::factory()->create([
            'agency_id' => $otherAgency->id,
            'name' => 'Foreign Employer',
        ]);
        $a = $this->applicant();

        $html = $this->statusTabHtml($a);

        $this->assertStringContainsString('Gulf Employer One', $html);
        $this->assertStringNotContainsString('Foreign Employer', $html);
    }

    #[Test]
    public function status_update_saves_employer_id(): void
    {
        $emp = Employer::factory()->create(['agency_id' => $this->agency->id]);
        $a = $this->applicant();

        $resp = $this->actingAs($this->user)->from(route('applicants.show', $a))->patch(
            route('applicants.status', $a),
            [
                'status_code' => 0,
                'employer_id' => $emp->id,
            ]
        );

        $resp->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'id' => $a->id,
            'employer_id' => $emp->id,
        ]);
    }

    #[Test]
    public function status_update_rejects_employer_from_another_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $foreignEmp = Employer::factory()->create(['agency_id' => $otherAgency->id]);
        $a = $this->applicant();

        $resp = $this->actingAs($this->user)->from(route('applicants.show', $a))->patch(
            route('applicants.status', $a),
            [
                'status_code' => 0,
                'employer_id' => $foreignEmp->id,
            ]
        );

        $resp->assertSessionHasErrors('employer_id');
        $this->assertDatabaseHas('applicants', [
            'id' => $a->id,
            'employer_id' => null,
        ]);
    }

    #[Test]
    public function status_update_rejects_nonexistent_employer(): void
    {
        $a = $this->applicant();

        $resp = $this->actingAs($this->user)->from(route('applicants.show', $a))->patch(
            route('applicants.status', $a),
            [
                'status_code' => 0,
                'employer_id' => 999999,
            ]
        );

        $resp->assertSessionHasErrors('employer_id');
        $this->assertDatabaseHas('applicants', [
            'id' => $a->id,
            'employer_id' => null,
        ]);
    }

    // ---- Status options (Status tab) ----

    #[Test]
    public function status_tab_renders_all_statuses_when_none_configured(): void
    {
        $this->makeStatus(900, 'Only A');
        $html = $this->statusTabHtml($this->applicant());

        $this->assertStringContainsString('Only A', $html);
    }

    #[Test]
    public function status_tab_renders_full_status_list_even_when_agency_restricts(): void
    {
        // Toybits report 2026-08-10: the Status tab dropdown must match the
        // Add/Edit form — the FULL status list, never the agency-configured
        // subset (which was hiding statuses like Repatriated).
        $this->makeStatus(900, 'Only A');
        $this->makeStatus(901, 'Only B');
        $this->setDefaults(['status_codes' => [900]]);

        $html = $this->statusTabHtml($this->applicant());

        $this->assertStringContainsString('Only A', $html);
        $this->assertStringContainsString('Only B', $html);
    }

    private function setDefaults(array $defaults): void
    {
        $settings = $this->agency->settings ?? [];
        $settings['applicant_form_defaults'] = $defaults;
        $this->agency->update(['settings' => $settings]);
    }

    private function makeStatus(int $code, string $label): StatusCode
    {
        return StatusCode::create([
            'code' => $code,
            'label' => $label,
            'sort_order' => $code,
        ]);
    }
}
