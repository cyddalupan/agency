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
 * LANDAS "Personal Information" — PI:1 Basic Information tab: Language (TDD).
 *
 * The default Basic Information tab must support MULTIPLE languages per
 * applicant (hasMany, Add toggle), each with a proficiency dropdown
 * (beginner / intermediate / expert), and list them back in the tab.
 */
class PersonalInformationLanguageTest extends TestCase
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
    public function basic_tab_renders_language_section(): void
    {
        $html = $this->getShowHtml();

        $this->assertStringContainsString('Language', $html);
    }

    #[Test]
    public function language_form_includes_proficiency_dropdown(): void
    {
        $html = $this->getShowHtml();

        // Proficiency dropdown must offer the three levels.
        $this->assertStringContainsString('Beginner', $html);
        $this->assertStringContainsString('Intermediate', $html);
        $this->assertStringContainsString('Expert', $html);
    }

    #[Test]
    public function multiple_languages_are_supported_and_listed(): void
    {
        // Create two languages for the applicant through the relation.
        $this->applicant->languages()->create(['agency_id' => $this->agency->id, 'name' => 'Filipino', 'proficiency' => 'expert']);
        $this->applicant->languages()->create(['agency_id' => $this->agency->id, 'name' => 'English', 'proficiency' => 'intermediate']);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        // Both languages appear in the Basic tab list.
        $this->assertStringContainsString('Filipino', $html);
        $this->assertStringContainsString('English', $html);
    }

    #[Test]
    public function language_can_be_stored_via_sub_store_route(): void
    {
        // The language must exist in the Settings list (item 5 restriction).
        \App\Models\Language::create(['name' => 'Arabic']);

        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'languages']), [
                'name'        => 'Arabic',
                'proficiency' => 'beginner',
            ])
            ->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('applicant_languages', [
            'applicant_id' => $this->applicant->id,
            'name'         => 'Arabic',
            'proficiency'  => 'beginner',
        ]);
    }
}
