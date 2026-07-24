<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Country;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantPreferredCountryPositionTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Country $country;
    private Position $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $this->country = Country::factory()->create(['name' => 'Saudi Arabia']);
        $this->position = Position::factory()->create(['name' => 'Driver']);
    }

    // ─── CREATE FORM ───────────────────────────────────────────────

    #[Test]
    public function create_form_has_preferred_country_dropdown(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.create'));

        $response->assertOk();
        $response->assertSee('country_id');
        $response->assertSee('Preferred Country');
    }

    #[Test]
    public function create_form_has_preferred_position_dropdown(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.create'));

        $response->assertOk();
        $response->assertSee('position_id');
        $response->assertSee('Preferred Position');
    }

    #[Test]
    public function create_form_shows_countries_in_dropdown(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.create'));

        $response->assertSee('Saudi Arabia');
    }

    #[Test]
    public function create_form_shows_positions_in_dropdown(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.create'));

        $response->assertSee('Driver');
    }

    #[Test]
    public function store_saves_country_id(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name' => 'Juan',
                'last_name'  => 'Dela Cruz',
                'country_id' => $this->country->id,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'first_name' => 'Juan',
            'country_id' => $this->country->id,
        ]);
    }

    #[Test]
    public function store_saves_position_id(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'  => 'Maria',
                'last_name'   => 'Santos',
                'position_id' => $this->position->id,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'first_name'  => 'Maria',
            'position_id' => $this->position->id,
        ]);
    }

    #[Test]
    public function store_country_id_can_be_null(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name' => 'Test',
                'last_name'  => 'User',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'first_name' => 'Test',
            'country_id' => null,
        ]);
    }

    #[Test]
    public function store_position_id_can_be_null(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name' => 'Test',
                'last_name'  => 'User',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'first_name'  => 'Test',
            'position_id' => null,
        ]);
    }

    // ─── EDIT / UPDATE FORM ────────────────────────────────────────

    #[Test]
    public function edit_form_has_preferred_country_dropdown(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'country_id' => $this->country->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.edit', $applicant));

        $response->assertOk();
        $response->assertSee('Saudi Arabia');
    }

    #[Test]
    public function edit_form_has_preferred_position_dropdown(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'position_id' => $this->position->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.edit', $applicant));

        $response->assertOk();
        $response->assertSee('Driver');
    }

    #[Test]
    public function update_changes_country_id(): void
    {
        $country2 = Country::factory()->create(['name' => 'UAE']);
        $applicant = Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'country_id' => $this->country->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), [
                'first_name' => $applicant->first_name,
                'last_name'  => $applicant->last_name,
                'country_id' => $country2->id,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'id'         => $applicant->id,
            'country_id' => $country2->id,
        ]);
    }

    #[Test]
    public function update_changes_position_id(): void
    {
        $position2 = Position::factory()->create(['name' => 'Caregiver']);
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'position_id' => $this->position->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), [
                'first_name'  => $applicant->first_name,
                'last_name'   => $applicant->last_name,
                'position_id' => $position2->id,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'id'          => $applicant->id,
            'position_id' => $position2->id,
        ]);
    }

    // ─── SHOW PAGE ─────────────────────────────────────────────────

    #[Test]
    public function show_page_displays_preferred_country_and_position(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'country_id'   => $this->country->id,
            'position_id'  => $this->position->id,
            'has_passport' => 'with',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant));

        $response->assertOk();
        $response->assertSee('Saudi Arabia');
        $response->assertSee('Driver');
    }

    // ─── CSV EXPORT ─────────────────────────────────────────────────

    #[Test]
    public function export_contains_preferred_country_column(): void
    {
        app()->instance('tenant_agency', $this->agency);

        Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'country_id' => $this->country->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Saudi Arabia', $content);
    }

    #[Test]
    public function export_contains_preferred_position_column(): void
    {
        app()->instance('tenant_agency', $this->agency);

        Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'position_id' => $this->position->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Driver', $content);
    }
}
