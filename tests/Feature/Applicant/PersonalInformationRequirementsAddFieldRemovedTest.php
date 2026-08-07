<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ApplicantRequirement as Requirement;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS "For Fixing Personal Info" — checklist item 6 (TDD).
 *
 * The Requirements tab offered an "Add" field/form with a file upload.
 * That upload is already handled by the Upload Files tab, so the
 * Requirements "Add field" UI must be removed (list remains viewable).
 */
class PersonalInformationRequirementsAddFieldRemovedTest extends TestCase
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
            'agency_id' => $this->agency->id,
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    private function getPageHtml(): string
    {
        return $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();
    }

    #[Test]
    public function requirements_tab_has_no_add_field_form(): void
    {
        $html = $this->getPageHtml();

        // The Requirements tab's "Add" button + add-form container are gone.
        $this->assertStringNotContainsString('form-requirements', $html);
        $this->assertStringNotContainsString(
            "document.getElementById('form-requirements')", $html
        );
    }

    #[Test]
    public function requirements_list_is_still_displayed(): void
    {
        Requirement::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicant->id,
            'type'         => 'visa',
            'reference_no' => 'VISA-001',
        ]);

        $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->assertSee('Requirements')
            ->assertSee('VISA-001');
    }
}
