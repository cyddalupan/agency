<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ApplicantLanguage;
use App\Models\ApplicantSkill;
use App\Models\Branch;
use App\Models\CivilStatus;
use App\Models\Language;
use App\Models\Nationality;
use App\Models\Religion;
use App\Models\Skill;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * "For Fixing "Personal Info'" card follow-up (Cyd's bug report):
 *
 * The Add AND Edit applicant forms must both contain:
 *  - the Branch dropdown (always visible, single source of truth)
 *  - Civil Status / Nationality / Religion dropdowns (Settings-backed)
 *  - Family information (mother/father name + occupation)
 *  - Skills (restricted to the Settings-configured list)
 *  - Languages (restricted to the Settings-configured list)
 */
class PersonalInformationCreateEditFormsTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Branch $branch;
    private CivilStatus $civil;
    private Nationality $nationality;
    private Religion $religion;
    private Skill $skillA;
    private Skill $skillB;
    private Language $langA;
    private Language $langB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $this->branch      = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Alabang Branch']);
        $this->civil       = CivilStatus::create(['name' => 'Single']);
        $this->nationality = Nationality::create(['name' => 'Filipino']);
        $this->religion    = Religion::create(['name' => 'Roman Catholic']);
        $this->skillA      = Skill::factory()->create(['name' => 'Cooking']);
        $this->skillB      = Skill::factory()->create(['name' => 'Child Care']);
        $this->langA       = Language::factory()->create(['name' => 'English']);
        $this->langB       = Language::factory()->create(['name' => 'Arabic']);
    }

    #[Test]
    public function create_form_has_branch_civil_nationality_religion_family_skills_languages(): void
    {
        $html = $this->actingAs($this->user)->get(route('applicants.create'))->getContent();

        // Branch dropdown (always present)
        $this->assertStringContainsString('name="branch_id"', $html);
        $this->assertStringContainsString($this->branch->name, $html);
        // No legacy free-text branch textarea
        $this->assertStringNotContainsString('<textarea name="branch"', $html);

        // Civil Status / Nationality / Religion
        $this->assertStringContainsString('name="civil_status_id"', $html);
        $this->assertStringContainsString('name="nationality_id"', $html);
        $this->assertStringContainsString('name="religion_id"', $html);
        $this->assertStringContainsString($this->nationality->name, $html);
        $this->assertStringContainsString($this->religion->name, $html);

        // Family information
        $this->assertStringContainsString('name="mother_name"', $html);
        $this->assertStringContainsString('name="mother_occupation"', $html);
        $this->assertStringContainsString('name="father_name"', $html);
        $this->assertStringContainsString('name="father_occupation"', $html);

        // Skills & Languages (restricted to Settings lists)
        $this->assertStringContainsString('name="skills[]"', $html);
        $this->assertStringContainsString($this->skillA->name, $html);
        $this->assertStringContainsString($this->skillB->name, $html);
        $this->assertStringContainsString('name="languages[]"', $html);
        $this->assertStringContainsString($this->langA->name, $html);
        $this->assertStringContainsString($this->langB->name, $html);
    }

    #[Test]
    public function edit_form_has_branch_civil_nationality_religion_family_skills_languages(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'source'    => 'Branch',
        ]);

        $html = $this->actingAs($this->user)->get(route('applicants.edit', $applicant))->getContent();

        // Branch dropdown + pre-selected saved branch
        $this->assertStringContainsString('name="branch_id"', $html);
        $this->assertStringContainsString('value="' . $this->branch->id . '" selected', $html);
        $this->assertStringNotContainsString('<textarea name="branch"', $html);

        // Civil / Nationality / Religion
        $this->assertStringContainsString('name="civil_status_id"', $html);
        $this->assertStringContainsString('name="nationality_id"', $html);
        $this->assertStringContainsString('name="religion_id"', $html);

        // Family information
        $this->assertStringContainsString('name="mother_name"', $html);
        $this->assertStringContainsString('name="mother_occupation"', $html);
        $this->assertStringContainsString('name="father_name"', $html);
        $this->assertStringContainsString('name="father_occupation"', $html);

        // Skills & Languages
        $this->assertStringContainsString('name="skills[]"', $html);
        $this->assertStringContainsString('name="languages[]"', $html);
    }

    #[Test]
    public function store_persists_branch_civil_nationality_religion_family_skills_languages(): void
    {
        $response = $this->actingAs($this->user)->post(route('applicants.store'), [
            'first_name'         => 'Juan',
            'last_name'          => 'Cruz',
            'source'             => 'Branch',
            'branch_id'          => $this->branch->id,
            'civil_status_id'    => $this->civil->id,
            'nationality_id'     => $this->nationality->id,
            'religion_id'        => $this->religion->id,
            'mother_name'        => 'Maria Cruz',
            'mother_occupation'  => 'Housewife',
            'father_name'        => 'Pedro Cruz',
            'father_occupation'  => 'Driver',
            'skills'             => [$this->skillA->name, $this->skillB->name],
            'languages'          => [$this->langA->name, $this->langB->name],
        ]);

        $response->assertRedirect();

        $applicant = Applicant::first();
        $this->assertNotNull($applicant);
        $this->assertSame($this->branch->id, $applicant->branch_id);
        $this->assertSame($this->civil->id, $applicant->civil_status_id);
        $this->assertSame($this->nationality->id, $applicant->nationality_id);
        $this->assertSame($this->religion->id, $applicant->religion_id);
        $this->assertSame('Maria Cruz', $applicant->mother_name);
        $this->assertSame('Housewife', $applicant->mother_occupation);
        $this->assertSame('Pedro Cruz', $applicant->father_name);
        $this->assertSame('Driver', $applicant->father_occupation);

        $this->assertTrue($applicant->skills->contains('skill_name', 'Cooking'));
        $this->assertTrue($applicant->skills->contains('skill_name', 'Child Care'));
        $this->assertTrue($applicant->languages->contains('name', 'English'));
        $this->assertTrue($applicant->languages->contains('name', 'Arabic'));
    }

    #[Test]
    public function update_persists_branch_civil_nationality_religion_family_skills_languages(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)->patch(route('applicants.update', $applicant), [
            'first_name'         => 'Juan',
            'last_name'          => 'Cruz',
            'source'             => 'Branch',
            'branch_id'          => $this->branch->id,
            'civil_status_id'    => $this->civil->id,
            'nationality_id'     => $this->nationality->id,
            'religion_id'        => $this->religion->id,
            'mother_name'        => 'Maria Cruz',
            'father_name'        => 'Pedro Cruz',
            'skills'             => [$this->skillA->name],
            'languages'          => [$this->langA->name],
        ]);

        $response->assertRedirect();
        $applicant->refresh();

        $this->assertSame($this->branch->id, $applicant->branch_id);
        $this->assertSame($this->civil->id, $applicant->civil_status_id);
        $this->assertSame($this->nationality->id, $applicant->nationality_id);
        $this->assertSame($this->religion->id, $applicant->religion_id);
        $this->assertSame('Maria Cruz', $applicant->mother_name);
        $this->assertSame('Pedro Cruz', $applicant->father_name);
        $this->assertTrue($applicant->skills->contains('skill_name', 'Cooking'));
        $this->assertTrue($applicant->languages->contains('name', 'English'));
    }

    #[Test]
    public function edit_form_preselects_existing_skills_languages_family(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'        => $this->agency->id,
            'mother_name'      => 'Elena Dizon',
            'father_name'      => 'Ramon Dizon',
            'nationality_id'   => $this->nationality->id,
            'religion_id'      => $this->religion->id,
            'civil_status_id'  => $this->civil->id,
        ]);
        $applicant->skills()->create(['agency_id' => $this->agency->id, 'skill_name' => 'Cooking']);
        $applicant->languages()->create(['agency_id' => $this->agency->id, 'name' => 'English']);

        $html = $this->actingAs($this->user)->get(route('applicants.edit', $applicant))->getContent();

        $this->assertStringContainsString('Elena Dizon', $html);
        $this->assertStringContainsString('Ramon Dizon', $html);
        // Saved skills/languages pre-selected
        $this->assertStringContainsString('value="Cooking"', $html);
        $this->assertStringContainsString('value="English"', $html);
        $this->assertMatchesRegularExpression('/value="Cooking"[^>]*checked/', $html);
        $this->assertMatchesRegularExpression('/value="English"[^>]*checked/', $html);
    }
}
