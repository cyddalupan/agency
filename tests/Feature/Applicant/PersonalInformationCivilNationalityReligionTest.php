<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\CivilStatus;
use App\Models\Nationality;
use App\Models\Religion;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS "For Fixing Personal Info" — checklist item 2 (TDD).
 *
 * Civil Status, Nationality, Religion should be available on the
 * Add Applicant form as dropdowns populated from the reference
 * (Settings-managed) tables.
 */
class PersonalInformationCivilNationalityReligionTest extends TestCase
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

    #[Test]
    public function add_applicant_form_has_civil_status_dropdown(): void
    {
        CivilStatus::create(['name' => 'Single']);
        CivilStatus::create(['name' => 'Married']);

        $this->actingAs($this->user)
            ->get(route('applicants.create'))
            ->assertOk()
            ->assertSee('name="civil_status_id"', false)
            ->assertSee('Single')
            ->assertSee('Married');
    }

    #[Test]
    public function add_applicant_form_has_nationality_dropdown(): void
    {
        Nationality::create(['name' => 'Filipino']);

        $this->actingAs($this->user)
            ->get(route('applicants.create'))
            ->assertOk()
            ->assertSee('name="nationality_id"', false)
            ->assertSee('Filipino');
    }

    #[Test]
    public function add_applicant_form_has_religion_dropdown(): void
    {
        Religion::create(['name' => 'Roman Catholic']);

        $this->actingAs($this->user)
            ->get(route('applicants.create'))
            ->assertOk()
            ->assertSee('name="religion_id"', false)
            ->assertSee('Roman Catholic');
    }

    #[Test]
    public function store_persists_civil_status_nationality_and_religion(): void
    {
        $cs = CivilStatus::create(['name' => 'Single']);
        $nat = Nationality::create(['name' => 'Filipino']);
        $rel = Religion::create(['name' => 'Roman Catholic']);

        $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'      => 'Juan',
                'last_name'       => 'Dela Cruz',
                'civil_status_id' => $cs->id,
                'nationality_id'  => $nat->id,
                'religion_id'     => $rel->id,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $applicant = Applicant::first();
        $this->assertSame($cs->id, $applicant->civil_status_id);
        $this->assertSame($nat->id, $applicant->nationality_id);
        $this->assertSame($rel->id, $applicant->religion_id);

        $this->assertSame('Single', $applicant->civilStatus?->name);
        $this->assertSame('Filipino', $applicant->nationality?->name);
        $this->assertSame('Roman Catholic', $applicant->religion?->name);
    }
}
