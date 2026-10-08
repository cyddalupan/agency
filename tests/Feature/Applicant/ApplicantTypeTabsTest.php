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

    // ── Follow-up (2026-10-08): DH regression + tab order/emphasis ──────

    #[Test]
    public function legacy_household_position_applicant_shows_on_the_household_tab(): void
    {
        // A legacy DH row: applicant_type NULL, position on the domestic
        // roster. It must land on HOUSEHOLD, not SKILLED.
        $maid = Position::firstOrCreate(['name' => 'Maid'], ['category' => Position::CATEGORY_HOUSEHOLD]);
        $maid->update(['category' => Position::CATEGORY_HOUSEHOLD]);

        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'LegacyMaid',
            'applicant_type' => null,
            'position_id' => $maid->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'household']))
            ->assertOk()
            ->assertSee('LegacyMaid');

        $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'skilled']))
            ->assertOk()
            ->assertDontSee('LegacyMaid');
    }

    #[Test]
    public function household_position_is_excluded_from_the_skilled_tab_even_without_a_type(): void
    {
        $house = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'DomesticNoType',
            'applicant_type' => null,
        ]);
        $house->position()->associate(Position::where('name', 'Domestic Helper')->first());
        $house->save();

        $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'skilled']))
            ->assertOk()
            ->assertDontSee('DomesticNoType');
    }

    #[Test]
    public function index_defaults_to_the_skilled_tab(): void
    {
        // HOUSEHOLD is rendered first, but SKILLED remains the default landing
        // tab (backward compatible with the existing list behaviour).
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'DefaultHouse',
            'applicant_type' => Applicant::TYPE_HOUSEHOLD,
        ]);
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'DefaultSkill',
            'applicant_type' => Applicant::TYPE_SKILLED,
        ]);

        $this->actingAs($this->admin)
            ->get(route('applicants.index'))
            ->assertOk()
            ->assertSee('DefaultSkill')
            ->assertDontSee('DefaultHouse');
    }

    #[Test]
    public function household_tab_is_rendered_before_skilled(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('applicants.index'))
            ->assertOk()
            ->getContent();

        $householdPos = strpos($html, 'HOUSEHOLD');
        $skilledPos = strpos($html, 'SKILLED');

        $this->assertNotFalse($householdPos, 'HOUSEHOLD tab should render');
        $this->assertNotFalse($skilledPos, 'SKILLED tab should render');
        $this->assertLessThan($skilledPos, $householdPos, 'HOUSEHOLD tab must render before SKILLED');
    }
}
