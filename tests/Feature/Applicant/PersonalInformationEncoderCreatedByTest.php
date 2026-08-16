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
 * LANDAS "For Fixing Personal Info" — checklist item 7 (TDD).
 *
 * Encoder: saved in DB but not visible to users as an input. Use Laravel's
 * default `created_by` convention (auto-set to the auth user) and surface
 * Created By + timestamp columns on the List of Applicants.
 */
class PersonalInformationEncoderCreatedByTest extends TestCase
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
    public function create_form_does_not_expose_encoder_input(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('name="encoder"', $html);
    }

    #[Test]
    public function edit_form_does_not_expose_encoder_input(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('applicants.edit', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('name="encoder"', $html);
    }

    #[Test]
    public function creating_an_applicant_sets_created_by_to_the_auth_user(): void
    {
        $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name' => 'Maria',
                'last_name'  => 'Santos',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $applicant = Applicant::where('first_name', 'Maria')->first();
        $this->assertNotNull($applicant);
        $this->assertSame($this->user->id, $applicant->created_by);
    }

    #[Test]
    public function list_of_applicants_shows_encoder_column_and_hides_created_at(): void
    {
        $this->applicant->update([
            'created_by' => $this->user->id,
        ]);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.index'))
            ->assertOk()
            ->getContent();

        // Created By was renamed to Encoder; Created At is hidden (Toybits 2026-08-15).
        $this->assertStringContainsString('Encoder', $html);
        $this->assertStringNotContainsString('Created By', $html);
        $this->assertStringNotContainsString('Created At', $html);
        // The encoder value (auth user name) is still displayed, not editable.
        $this->assertStringContainsString($this->user->name, $html);
    }
}
