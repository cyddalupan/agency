<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantHasPassportTest extends TestCase
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

    // ─── CREATE FORM ───────────────────────────────────────────────

    #[Test]
    public function create_form_has_passport_dropdown(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.create'));

        $response->assertOk();
        $response->assertSee('has_passport');
        $response->assertSee('With Passport');
        $response->assertSee('Without Passport');
    }

    #[Test]
    public function store_saves_has_passport_with_value(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'   => 'Juan',
                'last_name'    => 'Dela Cruz',
                'has_passport' => 'with',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('applicants.index'));
        $this->assertDatabaseHas('applicants', [
            'first_name'   => 'Juan',
            'has_passport' => 'with',
        ]);
    }

    #[Test]
    public function store_saves_has_passport_without_value(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'   => 'Maria',
                'last_name'    => 'Santos',
                'has_passport' => 'without',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('applicants.index'));
        $this->assertDatabaseHas('applicants', [
            'first_name'   => 'Maria',
            'has_passport' => 'without',
        ]);
    }

    #[Test]
    public function store_has_passport_defaults_to_null_when_omitted(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name' => 'Test',
                'last_name'  => 'User',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('applicants.index'));
        $this->assertDatabaseHas('applicants', [
            'first_name'   => 'Test',
            'has_passport' => null,
        ]);
    }

    // ─── EDIT / UPDATE FORM ────────────────────────────────────────

    #[Test]
    public function edit_form_has_passport_dropdown_with_selection(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => 'with',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.edit', $applicant));

        $response->assertOk();
        $response->assertSee('has_passport');
        $response->assertSee('With Passport');
        $response->assertSee('Without Passport');
    }

    #[Test]
    public function update_changes_has_passport_value(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => 'with',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), [
                'first_name'   => $applicant->first_name,
                'last_name'    => $applicant->last_name,
                'has_passport' => 'without',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('applicants.index'));
        $this->assertDatabaseHas('applicants', [
            'id'           => $applicant->id,
            'has_passport' => 'without',
        ]);
    }

    // ─── SHOW PAGE ─────────────────────────────────────────────────

    #[Test]
    public function show_page_displays_has_passport_badge_when_with(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => 'with',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant));

        $response->assertOk();
        $response->assertSee('With Passport');
    }

    #[Test]
    public function show_page_displays_no_passport_badge_when_without(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => 'without',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant));

        $response->assertOk();
        $response->assertSee('Without Passport');
    }

    #[Test]
    public function show_page_shows_passport_sub_form_when_has_passport(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => 'with',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant));

        $response->assertOk();
        $response->assertSee('Passport');
        $response->assertSee('Passport No.');
    }

    #[Test]
    public function show_page_hides_passport_sub_form_when_no_passport(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => 'without',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant));

        $response->assertOk();
        $response->assertDontSee('Passport No.');
    }

    #[Test]
    public function show_page_shows_passport_sub_form_when_has_passport_null(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant));

        $response->assertOk();
        $response->assertSee('Passport No.');
    }

    // ─── CSV EXPORT ─────────────────────────────────────────────────

    #[Test]
    public function export_contains_has_passport_column(): void
    {
        app()->instance('tenant_agency', $this->agency);

        Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => 'with',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Has Passport', $content);
        $this->assertStringContainsString('with', $content);
    }
}
