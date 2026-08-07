<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS "Personal Information" — PI:2 Requirements tab (TDD).
 *
 * The Requirements tab must include: Resume/CV, Passport (Number, Date
 * Issued, Place Issued, Expiry Date), NBI (NBI Number, Date Issued, Expiry
 * Date), E-REG / PEOS / Info sheet / Birth Certificate / Marriage
 * Certificate checkboxes, and a "Save Requirements" button. NBI records are
 * stored via the sub.store route; checkbox states persist via a dedicated
 * requirements save route.
 */
class PersonalInformationRequirementsTest extends TestCase
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
        ]);
        $this->applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => 'with',
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
    public function requirements_tab_renders_passport_fields(): void
    {
        $html = $this->getShowHtml();

        foreach (['Passport No.', 'Passport', 'Issue Date', 'Expiry Date'] as $s) {
            $this->assertStringContainsString($s, $html, "'{$s}' should render in the Requirements tab");
        }
    }

    #[Test]
    public function requirements_tab_renders_nbi_section(): void
    {
        $html = $this->getShowHtml();

        $this->assertStringContainsString('NBI', $html);
        $this->assertStringContainsString('NBI No.', $html);
        $this->assertStringContainsString('Issue Date', $html);
        $this->assertStringContainsString('Expiry Date', $html);
    }

    #[Test]
    public function requirements_tab_renders_resume_cv_section(): void
    {
        $this->assertStringContainsString('Resume', $this->getShowHtml());
    }

    #[Test]
    public function requirements_tab_renders_all_checkboxes(): void
    {
        $html = $this->getShowHtml();

        foreach (['E-REG', 'PEOS', 'Info sheet', 'Birth Certificate', 'Marriage Certificate'] as $label) {
            // Labels are rendered in uppercase because of CSS text-transform.
            $this->assertStringContainsString(strtoupper($label), strtoupper($html), "Checkbox '{$label}' should render in the Requirements tab");
        }
    }

    #[Test]
    public function requirements_tab_has_save_requirements_button(): void
    {
        $html = $this->getShowHtml();

        $this->assertStringContainsString('Save Requirements', $html);
    }

    #[Test]
    public function nbi_can_be_stored_via_sub_store_route(): void
    {
        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'nbi']), [
                'nbi_no'     => 'NBI-2026-12345',
                'issue_date' => '2026-01-15',
                'expiry_date' => '2036-01-15',
            ])
            ->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('applicant_nbis', [
            'applicant_id' => $this->applicant->id,
            'nbi_no'       => 'NBI-2026-12345',
        ]);

        // Dates are cast to date columns; verify via the model's formatted
        // output rather than a raw DB string comparison.
        $nbi = $this->applicant->nbi()->first();
        $this->assertNotNull($nbi);
        $this->assertEquals('2026-01-15', $nbi->issue_date?->format('Y-m-d'));
        $this->assertEquals('2036-01-15', $nbi->expiry_date?->format('Y-m-d'));
    }

    #[Test]
    public function checkbox_states_persist_via_save_requirements_route(): void
    {
        $this->actingAs($this->user)
            ->patch(route('applicants.requirements.update', $this->applicant), [
                'e_reg'          => '1',
                'peos'           => '1',
                'info_sheet'     => '0',
                'birth_certificate' => '1',
                'marriage_certificate' => '0',
            ])
            ->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('applicants', [
            'id'      => $this->applicant->id,
            'e_reg'   => 1,
            'peos'    => 1,
            'info_sheet' => 0,
            'birth_certificate' => 1,
            'marriage_certificate' => 0,
        ]);
    }

    #[Test]
    public function checked_checklist_items_render_checked_by_round_trip(): void
    {
        // Regression: the checklist boxes must re-render as checked after being
        // saved, so an operator can see which items are completed. This is the
        // "check items" behaviour — a saved value must come back as checked.
        $this->applicant->update([
            'e_reg'              => true,
            'birth_certificate'  => true,
            'marriage_certificate' => true,
            'peos'               => false,
            'info_sheet'         => false,
        ]);

        $html = $this->getShowHtml();

        foreach (['e_reg', 'birth_certificate', 'marriage_certificate'] as $name) {
            $this->assertMatchesRegularExpression(
                '/name="' . $name . '"[^>]*checked/i',
                $html,
                "'{$name}' checkbox should render checked after being saved"
            );
        }
        foreach (['peos', 'info_sheet'] as $name) {
            $this->assertDoesNotMatchRegularExpression(
                '/name="' . $name . '"[^>]*checked/i',
                $html,
                "'{$name}' checkbox should render unchecked when not saved"
            );
        }
    }

    #[Test]
    public function empty_checklist_submit_resets_all_flags_to_false(): void
    {
        // Prior state: all flags checked.
        $this->applicant->update([
            'e_reg' => true,
            'peos' => true,
            'info_sheet' => true,
            'birth_certificate' => true,
            'marriage_certificate' => true,
        ]);

        // Submit the checklist with nothing checked (absence = unchecked).
        $this->actingAs($this->user)
            ->patch(route('applicants.requirements.update', $this->applicant), [])
            ->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('applicants', [
            'id'      => $this->applicant->id,
            'e_reg'   => 0,
            'peos'    => 0,
            'info_sheet' => 0,
            'birth_certificate' => 0,
            'marriage_certificate' => 0,
        ]);
    }

    #[Test]
    public function save_requirements_does_not_wipe_other_applicant_fields(): void
    {
        $this->applicant->update(['source' => 'Branch', 'encoder' => 'Cyd']);

        $this->actingAs($this->user)
            ->patch(route('applicants.requirements.update', $this->applicant), [
                'e_reg' => '1',
            ]);

        $this->assertDatabaseHas('applicants', [
            'id'     => $this->applicant->id,
            'source' => 'Branch',
            'encoder'=> 'Cyd',
            'e_reg'  => 1,
        ]);
    }
}
