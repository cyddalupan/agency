<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Language;
use App\Models\Skill;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS "For Fixing Personal Info" — checklist items 4 & 5 (TDD).
 *
 * Skill and Language inputs must come from the Settings list only
 * (no free-text / custom entry).
 */
class PersonalInformationSkillsLanguagesRestrictedTest extends TestCase
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

    #[Test]
    public function skills_sub_form_is_a_settings_sourced_dropdown_not_free_text(): void
    {
        Skill::create(['name' => 'Caregiving']);
        Skill::create(['name' => 'Nursing']);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        // A settings-sourced dropdown, not a free-text input.
        $this->assertStringContainsString('<select name="skill_name"', $html);
        $this->assertStringContainsString('Caregiving', $html);
        $this->assertStringContainsString('Nursing', $html);
        $this->assertStringNotContainsString('<input type="text" name="skill_name"', $html);
    }

    #[Test]
    public function languages_sub_form_is_a_settings_sourced_dropdown_not_free_text(): void
    {
        Language::create(['name' => 'English']);
        Language::create(['name' => 'Arabic']);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('<select name="name"', $html);
        $this->assertStringContainsString('English', $html);
        $this->assertStringContainsString('Arabic', $html);
        // The languages sub-form is a Select (other sub-forms legitimately use
        // a free-text input named "name", so we cannot assert its absence
        // globally; we assert the languages select is present instead).
    }

    #[Test]
    public function storing_a_skill_not_in_settings_is_rejected(): void
    {
        Skill::create(['name' => 'Caregiving']);

        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'skills']), [
                'skill_name'  => 'NotInSettingsSkill',
                'proficiency' => 'intermediate',
            ])
            ->assertSessionHasErrors('skill_name', null, 'skills');
    }

    #[Test]
    public function storing_a_skill_from_settings_succeeds(): void
    {
        Skill::create(['name' => 'Caregiving']);

        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'skills']), [
                'skill_name'  => 'Caregiving',
                'proficiency' => 'intermediate',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('applicant_skills', [
            'applicant_id' => $this->applicant->id,
            'skill_name'   => 'Caregiving',
        ]);
    }

    #[Test]
    public function storing_a_language_not_in_settings_is_rejected(): void
    {
        Language::create(['name' => 'English']);

        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'languages']), [
                'name'        => 'Klingon',
                'proficiency' => 'beginner',
            ])
            ->assertSessionHasErrors('name', null, 'languages');
    }

    #[Test]
    public function storing_a_language_from_settings_succeeds(): void
    {
        Language::create(['name' => 'English']);

        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'languages']), [
                'name'        => 'English',
                'proficiency' => 'beginner',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('applicant_languages', [
            'applicant_id' => $this->applicant->id,
            'name'         => 'English',
        ]);
    }
}
