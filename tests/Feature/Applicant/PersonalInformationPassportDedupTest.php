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
 * LANDAS "For Fixing Personal Info" — checklist item 1 (TDD).
 *
 * The Edit Applicant form had the `has_passport` select duplicated (twice).
 * Per requirement: delete the duplicate (the top one in the 4-column row),
 * keep the Passport sub-form (details), and confirm no orphaned data.
 */
class PersonalInformationPassportDedupTest extends TestCase
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

    #[Test]
    public function edit_form_has_exactly_one_has_passport_dropdown(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('applicants.edit', $this->applicant))
            ->assertOk()
            ->getContent();

        $count = substr_count($html, 'name="has_passport"');
        $this->assertSame(1, $count, 'Expected exactly one has_passport select on the Edit form');
    }

    #[Test]
    public function passport_sub_form_details_are_still_available_on_show_page(): void
    {
        // The dedicated Passport sub-form lives in the Personal Info panel on
        // the SHOW page (not the edit form). It must remain after removing the
        // duplicate has_passport select from Edit.
        $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->assertSee('passport_no', false)
            ->assertSee('issue_date', false)
            ->assertSee('expiry_date', false)
            ->assertSee('place_of_issue', false);
    }

    #[Test]
    public function existing_has_passport_value_is_preserved_and_not_orphaned(): void
    {
        // The stored value is still readable from the model, and the Edit page
        // renders the surviving dropdown with the persisted value selected.
        $html = $this->actingAs($this->user)
            ->get(route('applicants.edit', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertSame('with', $this->applicant->fresh()->has_passport);
        $this->assertStringContainsString('value="with" selected', $html);
    }
}
