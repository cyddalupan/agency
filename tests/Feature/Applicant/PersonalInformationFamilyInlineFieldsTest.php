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
 * LANDAS "For Fixing Personal Info" — checklist item 3 (TDD).
 *
 * Family Information currently only had "Number of Siblings" as an inline
 * input. Add inline input fields for Mother's Name/Occupation and Father's
 * Name/Occupation directly on the Basic tab.
 */
class PersonalInformationFamilyInlineFieldsTest extends TestCase
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

    private function getBasicTabHtml(): string
    {
        return $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();
    }

    #[Test]
    public function basic_tab_has_inline_mother_fields(): void
    {
        $html = $this->getBasicTabHtml();
        $this->assertStringContainsString('name="mother_name"', $html);
        $this->assertStringContainsString('name="mother_occupation"', $html);
    }

    #[Test]
    public function basic_tab_has_inline_father_fields(): void
    {
        $html = $this->getBasicTabHtml();
        $this->assertStringContainsString('name="father_name"', $html);
        $this->assertStringContainsString('name="father_occupation"', $html);
    }

    #[Test]
    public function family_inline_fields_persist_via_basic_update(): void
    {
        $this->actingAs($this->user)
            ->patch(route('applicants.basic.update', $this->applicant), [
                'number_of_siblings' => 2,
                'mother_name'        => 'Corazon',
                'mother_occupation'  => 'Teacher',
                'father_name'        => 'Pedro',
                'father_occupation'  => 'Farmer',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('applicants.show', $this->applicant));

        $a = $this->applicant->fresh();
        $this->assertSame(2, (int) $a->number_of_siblings);
        $this->assertSame('Corazon', $a->mother_name);
        $this->assertSame('Teacher', $a->mother_occupation);
        $this->assertSame('Pedro', $a->father_name);
        $this->assertSame('Farmer', $a->father_occupation);
    }

    #[Test]
    public function stored_family_fields_are_displayed_on_basic_tab(): void
    {
        $this->applicant->update([
            'mother_name'       => 'Corazon',
            'mother_occupation' => 'Teacher',
            'father_name'       => 'Pedro',
            'father_occupation' => 'Farmer',
        ]);

        $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->assertSee('Corazon')
            ->assertSee('Teacher')
            ->assertSee('Pedro')
            ->assertSee('Farmer');
    }
}
