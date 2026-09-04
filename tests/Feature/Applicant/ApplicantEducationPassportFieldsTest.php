<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ApplicantPassport;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantEducationPassportFieldsTest extends TestCase
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
    }

    // ─── EDUCATION LEVEL ───────────────────────────────────────────

    #[Test]
    public function create_form_has_education_level_dropdown(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.create'));

        $response->assertOk();
        $response->assertSee('education_level');
        $response->assertSee('High School');
        $response->assertSee('Bachelor');
        $response->assertSee('Master');
    }

    #[Test]
    public function store_saves_education_level(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'      => 'Juan',
                'last_name'       => 'Dela Cruz',
                'education_level' => 'bachelor',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('applicants.index'));
        $this->assertDatabaseHas('applicants', [
            'first_name'      => 'Juan',
            'education_level' => 'bachelor',
        ]);
    }

    #[Test]
    public function store_rejects_invalid_education_level(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'      => 'Juan',
                'last_name'       => 'Dela Cruz',
                'education_level' => 'phd-of-unicorns',
            ]);

        $response->assertSessionHasErrors('education_level');
    }

    #[Test]
    public function edit_form_has_education_level_prefilled(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'       => $this->agency->id,
            'education_level' => 'master',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.edit', $applicant));

        $response->assertOk();
        $response->assertSee('education_level');
        $response->assertSee('Master');
    }

    #[Test]
    public function update_saves_education_level(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), [
                'first_name'      => $applicant->first_name,
                'last_name'       => $applicant->last_name,
                'education_level' => 'vocational',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('applicants.index'));
        $this->assertDatabaseHas('applicants', [
            'id'              => $applicant->id,
            'education_level' => 'vocational',
        ]);
    }

    // ─── PASSPORT DETAILS ON CREATE ────────────────────────────────

    #[Test]
    public function store_creates_passport_record_with_details(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'               => 'Maria',
                'last_name'                => 'Santos',
                'has_passport'             => 'with',
                'passport_no'              => 'P1234567A',
                'passport_issue_date'      => '2020-01-15',
                'passport_place_of_issue'  => 'DFA Manila',
                'passport_expiry_date'     => '2030-01-14',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('applicants.index'));

        $applicant = Applicant::where('first_name', 'Maria')->first();
        $this->assertNotNull($applicant);

        $this->assertDatabaseHas('applicant_passports', [
            'applicant_id'    => $applicant->id,
            'agency_id'       => $this->agency->id,
            'passport_no'     => 'P1234567A',
            'issue_date'      => '2020-01-15 00:00:00',
            'place_of_issue'  => 'DFA Manila',
            'expiry_date'     => '2030-01-14 00:00:00',
        ]);
    }

    #[Test]
    public function store_does_not_create_passport_record_when_without(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'   => 'Maria',
                'last_name'    => 'Santos',
                'has_passport' => 'without',
                'passport_no'  => 'P1234567A',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(0, ApplicantPassport::count());
    }

    // ─── PASSPORT DETAILS ON UPDATE ────────────────────────────────

    #[Test]
    public function update_creates_passport_record_when_missing(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => 'with',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), [
                'first_name'           => $applicant->first_name,
                'last_name'            => $applicant->last_name,
                'has_passport'         => 'with',
                'passport_no'          => 'P9999999Z',
                'passport_issue_date'  => '2021-03-01',
                'passport_expiry_date' => '2031-02-28',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicant_passports', [
            'applicant_id' => $applicant->id,
            'passport_no'  => 'P9999999Z',
        ]);
    }

    #[Test]
    public function update_removes_passport_record_when_marked_without(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => 'with',
        ]);

        ApplicantPassport::create([
            'agency_id'   => $this->agency->id,
            'applicant_id' => $applicant->id,
            'passport_no' => 'P1234567A',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), [
                'first_name'   => $applicant->first_name,
                'last_name'    => $applicant->last_name,
                'has_passport' => 'without',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('applicant_passports', [
            'applicant_id' => $applicant->id,
        ]);
    }

    // ─── SALARY + FRA/EMPLOYER ON FORMS ────────────────────────────

    #[Test]
    public function create_form_has_salary_and_employer_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.create'));

        $response->assertOk();
        $response->assertSee('expected_salary');
        $response->assertSee('employer_id');
    }

    #[Test]
    public function store_saves_expected_salary_and_employer(): void
    {
        $employer = \App\Models\Employer::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'      => 'Juan',
                'last_name'       => 'Dela Cruz',
                'expected_salary' => 25000,
                'employer_id'     => $employer->id,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'first_name'      => 'Juan',
            'expected_salary' => 25000.00,
            'employer_id'     => $employer->id,
        ]);
    }
}
