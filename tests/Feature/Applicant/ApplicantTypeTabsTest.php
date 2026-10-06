<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "Skilled Applicants" (2026-10-06):
 *  - Applicant module + Backout/Repat module split into HOUSEHOLD / SKILLED tabs.
 *  - Applicant Type (household|skilled) stored on create.
 *  - Position categories: household => Domestic Helper only; skilled => the rest.
 */
class ApplicantTypeTabsTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);
        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        // Fresh test DB has no positions — add a skilled one (the migration
        // already inserts the single household position "Domestic Helper").
        Position::firstOrCreate(['name' => 'Nurse'], ['category' => Position::CATEGORY_SKILLED]);
    }

    #[Test]
    public function household_position_is_the_only_household_category(): void
    {
        $household = Position::where('name', 'Domestic Helper')->first();
        $this->assertNotNull($household, 'Domestic Helper position should exist');
        $this->assertSame(Position::CATEGORY_HOUSEHOLD, $household->category);

        $this->assertGreaterThan(0, Position::skilled()->count());
        $this->assertFalse(
            Position::skilled()->where('name', 'Domestic Helper')->exists(),
            'Domestic Helper must not appear in the Skilled position list'
        );
    }

    #[Test]
    public function applicant_index_household_tab_only_lists_household(): void
    {
        $household = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'HouseholdOnly',
            'applicant_type' => Applicant::TYPE_HOUSEHOLD,
        ]);
        $skilled = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'SkilledOnly',
            'applicant_type' => Applicant::TYPE_SKILLED,
        ]);

        $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'household']))
            ->assertOk()
            ->assertSee('HouseholdOnly')
            ->assertDontSee('SkilledOnly');

        $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'skilled']))
            ->assertOk()
            ->assertSee('SkilledOnly')
            ->assertDontSee('HouseholdOnly');
    }

    #[Test]
    public function legacy_applicants_without_type_show_on_the_skilled_tab(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'LegacyNull',
            'applicant_type' => null,
        ]);

        $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'skilled']))
            ->assertOk()
            ->assertSee('LegacyNull');
    }

    #[Test]
    public function withdrawn_module_has_the_same_household_and_skilled_split(): void
    {
        Applicant::factory()->withStatus(38)->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'CancelledHousehold',
            'applicant_type' => Applicant::TYPE_HOUSEHOLD,
        ]);
        Applicant::factory()->withStatus(50)->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'BackoutSkilled',
            'applicant_type' => Applicant::TYPE_SKILLED,
        ]);

        $this->actingAs($this->admin)
            ->get(route('applicants.withdrawn', ['type' => 'household']))
            ->assertOk()
            ->assertSee('CancelledHousehold')
            ->assertDontSee('BackoutSkilled');

        $this->actingAs($this->admin)
            ->get(route('applicants.withdrawn', ['type' => 'skilled']))
            ->assertOk()
            ->assertSee('BackoutSkilled')
            ->assertDontSee('CancelledHousehold');
    }

    #[Test]
    public function create_form_offers_the_type_choice_and_position_categories(): void
    {
        $this->actingAs($this->admin)
            ->get(route('applicants.create'))
            ->assertOk()
            ->assertSee('name="applicant_type"', false)
            ->assertSee('data-category="household"', false)
            ->assertSee('data-category="skilled"', false);
    }

    #[Test]
    public function store_persists_the_selected_type(): void
    {
        $this->actingAs($this->admin)
            ->post(route('applicants.store'), [
                'first_name' => 'Typed',
                'last_name' => 'Applicant',
                'applicant_type' => 'household',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('applicants', [
            'first_name' => 'Typed',
            'applicant_type' => 'household',
        ]);
    }

    #[Test]
    public function store_defaults_to_skilled_when_omitted(): void
    {
        $this->actingAs($this->admin)
            ->post(route('applicants.store'), [
                'first_name' => 'NoType',
                'last_name' => 'Applicant',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('applicants', [
            'first_name' => 'NoType',
            'applicant_type' => 'skilled',
        ]);
    }
}
