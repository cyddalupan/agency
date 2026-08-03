<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use App\Models\Nationality;
use App\Models\Religion;
use App\Models\CivilStatus;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS "Personal Information" — PI:1 Basic Information tab: personal fields (TDD).
 *
 * The Basic Information tab must surface the core personal fields:
 * First/Middle/Last Name, Suffix, Birthdate, Civil Status, Gender,
 * Nationality, Religion, Email — and display stored values via relations.
 */
class PersonalInformationPersonalFieldsTest extends TestCase
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
            'first_name'   => 'Juan',
            'middle_name'  => 'Santos',
            'last_name'    => 'Dela Cruz',
            'suffix'       => 'Jr',
            'gender'       => 'Male',
            'email'        => 'juan@example.com',
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
    public function basic_tab_renders_personal_field_labels(): void
    {
        $html = $this->getShowHtml();

        foreach (['Civil Status', 'Nationality', 'Religion'] as $label) {
            $this->assertStringContainsString($label, $html, "Label '{$label}' should render in the Basic tab");
        }
    }

    #[Test]
    public function personal_field_values_are_displayed_via_relations(): void
    {
        // Wire reference lookups.
        $nat = Nationality::create(['name' => 'Filipino']);
        $rel = Religion::create(['name' => 'Roman Catholic']);
        $cs  = CivilStatus::create(['name' => 'Married']);

        $this->applicant->update([
            'nationality_id'  => $nat->id,
            'religion_id'     => $rel->id,
            'civil_status_id' => $cs->id,
        ]);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Filipino', $html);
        $this->assertStringContainsString('Roman Catholic', $html);
        $this->assertStringContainsString('Married', $html);
        // Suffix + Email should render too.
        $this->assertStringContainsString('Jr', $html);
        $this->assertStringContainsString('juan@example.com', $html);
    }
}
