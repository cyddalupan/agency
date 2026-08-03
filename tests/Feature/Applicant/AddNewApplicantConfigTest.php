<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\Position;
use App\Models\StatusCode;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * "LANDAS Add NEW APPLICANT" — per-agency configurable applicant form defaults.
 *
 * No hard-coded lists: positions/statuses/sources are configurable per agency
 * (selected from existing reference data), persisted on agencies.settings as
 * applicant_form_defaults. Firstimer/Ex-Abroad is a per-agency dropdown.
 */
class AddNewApplicantConfigTest extends TestCase
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

    private function makePosition(string $name): Position
    {
        return Position::create(['name' => $name]);
    }

    private function makeStatus(int $code, string $label): StatusCode
    {
        return StatusCode::create(['code' => $code, 'label' => $label, 'sort_order' => $code]);
    }

    private function setDefaults(array $defaults): void
    {
        $settings = $this->agency->settings ?? [];
        $settings['applicant_form_defaults'] = array_merge(
            $this->defaultConfig(),
            $defaults
        );
        $this->agency->update(['settings' => $settings]);
    }

    private function defaultConfig(): array
    {
        return [
            'position_ids'   => [],
            'status_codes'   => [],
            'sources'        => ['Facebook', 'Referral', 'Walk-in', 'Website', 'Other', 'Branch'],
            'enable_firstimer' => true,
            'firstimer_options' => ['Firstimer', 'Ex-Abroad'],
        ];
    }

    // ---- config helper ----

    #[Test]
    public function defaults_are_applied_when_agency_has_no_config(): void
    {
        $config = app_applicant_form_defaults($this->agency);

        $this->assertIsArray($config['sources']);
        $this->assertContains('Branch', $config['sources']);
        $this->assertTrue($config['enable_firstimer']);
        $this->assertContains('Firstimer', $config['firstimer_options']);
    }

    #[Test]
    public function defaults_are_resolved_from_authenticated_agency(): void
    {
        $position = $this->makePosition('Houseboy');
        $this->setDefaults(['position_ids' => [$position->id]]);

        $this->actingAs($this->user);

        $this->assertContains($position->id, app_applicant_form_defaults()['position_ids']);
    }

    // ---- create form rendering (no hardcoded lists) ----

    #[Test]
    public function create_form_renders_all_positions_regardless_of_agency_position_ids(): void
    {
        // Per Mjolnir "For Fixing" card: positions must show the FULL list. The
        // agency's ticked position_ids are informational only — they do NOT hide
        // the rest of the positions on the Add Applicant form.
        $houseboy = $this->makePosition('Houseboy');
        $loader   = $this->makePosition('Load and Unload Worker');
        $nurse    = $this->makePosition('Nurse');
        $this->setDefaults(['position_ids' => [$houseboy->id, $loader->id]]);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Houseboy', $html);
        $this->assertStringContainsString('Load and Unload Worker', $html);
        // All positions render — even ones not ticked for this agency.
        $this->assertStringContainsString('value="' . $nurse->id . '"', $html);
    }

    #[Test]
    public function create_form_renders_all_statuses_regardless_of_agency_status_codes(): void
    {
        // Per Mjolnir "For Fixing" card: statuses must show the FULL list. The
        // agency's ticked status_codes are informational only — they do NOT hide
        // the rest of the statuses on the Add Applicant form.
        $owwa  = $this->makeStatus(60, 'For OWWA Make-Up Class');
        $tesda = $this->makeStatus(61, 'For Tesda');
        $bio   = $this->makeStatus(62, 'For Biometric'); // not ticked for this agency
        $this->setDefaults(['status_codes' => [$owwa->code, $tesda->code]]);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('For OWWA Make-Up Class', $html);
        $this->assertStringContainsString('For Tesda', $html);
        // All statuses render — even ones not ticked for this agency.
        $this->assertStringContainsString('>' . $bio->label . '</option>', $html);
    }

    #[Test]
    public function create_form_renders_only_agencys_enabled_sources(): void
    {
        $this->setDefaults(['sources' => ['Facebook', 'Branch']]);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Facebook', $html);
        $this->assertStringContainsString('Branch', $html);
        $this->assertStringNotContainsString('value="Website"', $html);
        $this->assertStringNotContainsString('value="Other"', $html);
    }

    #[Test]
    public function unknown_or_typo_source_never_renders(): void
    {
        $this->setDefaults(['sources' => ['Facebook', 'NOT_A_REAL_SOURCE']]);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Facebook', $html);
        $this->assertStringNotContainsString('NOT_A_REAL_SOURCE', $html);
    }

    // ---- Source = Branch -> branch-select -> branch-scoped agent ----

    #[Test]
    public function source_branch_shows_branch_select_and_branch_scoped_agents(): void
    {
        $branchA = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $branchB = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $agentA = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branchA->id, 'status' => 'active']);
        $agentB = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branchB->id, 'status' => 'active']);

        $this->setDefaults(['sources' => ['Branch']]);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString($branchA->name, $html);
        $this->assertStringContainsString($branchB->name, $html);

        // Both agents present in dataset (filter is client-side by branch).
        $this->assertStringContainsString($agentA->name, $html);
        $this->assertStringContainsString($agentB->name, $html);
    }

    #[Test]
    public function branch_option_data_attribute_marks_branch_for_agent_filtering(): void
    {
        $branchA = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $agentA = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branchA->id, 'status' => 'active']);

        $this->setDefaults(['sources' => ['Branch']]);

        $html = $this->actingAs($this->user)->get(route('applicants.create'))->getContent();

        $this->assertStringContainsString('data-branch="' . $branchA->id . '"', $html);
    }

    // ---- Firstimer / Ex-Abroad ----

    #[Test]
    public function firstimer_dropdown_renders_when_enabled(): void
    {
        $this->setDefaults(['enable_firstimer' => true]);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Firstimer', $html);
        $this->assertStringContainsString('Ex-Abroad', $html);
    }

    #[Test]
    public function firstimer_dropdown_hidden_when_disabled(): void
    {
        $this->setDefaults(['enable_firstimer' => false]);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('Ex-Abroad', $html);
    }

    // ---- store: firstimer_type + source=Branch persists ----

    #[Test]
    public function store_persists_firstimer_type(): void
    {
        $this->setDefaults(['enable_firstimer' => true]);

        $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'    => 'Maria',
                'last_name'     => 'Reyes',
                'firstimer_type'=> 'firstimer',
                'source'        => 'Facebook',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('applicants', [
            'first_name'     => 'Maria',
            'last_name'      => 'Reyes',
            'firstimer_type' => 'firstimer',
            'agency_id'      => $this->agency->id,
        ]);
    }

    #[Test]
    public function store_rejects_invalid_firstimer_type(): void
    {
        $this->setDefaults(['enable_firstimer' => true]);

        $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'    => 'Maria',
                'last_name'     => 'Reyes',
                'firstimer_type'=> 'returning_fresh',
            ])
            ->assertSessionHasErrors('firstimer_type');
    }

    #[Test]
    public function store_rejects_agent_from_different_branch_than_selected(): void
    {
        $branchA = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $branchB = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $agentB = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branchB->id, 'status' => 'active']);

        $this->setDefaults(['sources' => ['Branch']]);

        $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name' => 'Maria',
                'last_name'  => 'Reyes',
                'source'     => 'Branch',
                'branch_id'  => $branchA->id,
                'agent_id'   => $agentB->id, // belongs to branchB, not branchA
            ])
            ->assertSessionHasErrors('agent_id');
    }

    // ---- Agency Settings UI (selectors over reference data) ----

    #[Test]
    public function settings_page_lists_existing_positions_as_selectable(): void
    {
        $this->makePosition('Houseboy');
        $this->makePosition('Nurse');

        $this->actingAs($this->user)
            ->get(route('settings.applicant-form-defaults'))
            ->assertOk()
            ->assertSee('Houseboy')
            ->assertSee('Nurse');
    }

    #[Test]
    public function settings_save_persists_agencys_selections(): void
    {
        $houseboy = $this->makePosition('Houseboy');
        $nurse    = $this->makePosition('Nurse');
        $owwa     = $this->makeStatus(60, 'For OWWA Make-Up Class');

        $this->actingAs($this->user)
            ->post(route('settings.applicant-form-defaults.update'), [
                'position_ids' => [$houseboy->id, $nurse->id],
                'status_codes' => [$owwa->code],
                'sources'      => ['Facebook', 'Branch'],
                'enable_firstimer' => '1',
            ])
            ->assertRedirect(route('settings.applicant-form-defaults'))
            ->assertSessionHas('success');

        $config = app_applicant_form_defaults($this->agency->fresh());
        $this->assertSame([$houseboy->id, $nurse->id], $config['position_ids']);
        $this->assertSame([$owwa->code], $config['status_codes']);
        $this->assertSame(['Facebook', 'Branch'], $config['sources']);
        $this->assertTrue($config['enable_firstimer']);
    }

    #[Test]
    public function settings_save_rejects_unknown_source_to_prevent_typos(): void
    {
        $this->actingAs($this->user)
            ->post(route('settings.applicant-form-defaults.update'), [
                'sources' => ['Facebook', 'NOT_A_REAL_SOURCE'],
            ])
            ->assertSessionHasErrors('sources.1');

        $config = app_applicant_form_defaults($this->agency->fresh());
        $this->assertNotContains('NOT_A_REAL_SOURCE', $config['sources']);
    }

    #[Test]
    public function two_agencies_keep_separate_defaults(): void
    {
        $houseboy = $this->makePosition('Houseboy');

        $agencyB = Agency::factory()->create();
        $userB   = User::factory()->create(['agency_id' => $agencyB->id, 'user_type' => 'admin']);

        // Agency A enables Houseboy.
        $this->actingAs($this->user)
            ->post(route('settings.applicant-form-defaults.update'), ['position_ids' => [$houseboy->id]]);

        // Agency B has its own config (does not include Houseboy).
        $htmlB = $this->actingAs($userB)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('value="' . $houseboy->id . '">Houseboy', $htmlB);
    }

    #[Test]
    public function default_seeder_fills_agencys_config_with_landas_defaults(): void
    {
        $houseboy = $this->makePosition('Houseboy');
        $loader   = $this->makePosition('Load and Unload Worker');
        // Status codes 45-52 already seeded by StatusCodesSeeder in setUp().
        $owwa = StatusCode::where('label', 'For OWWA Make-Up Class')->firstOrFail();

        (new \Database\Seeders\ApplicantFormDefaultsSeeder())->run();

        $config = app_applicant_form_defaults($this->agency->fresh());
        $this->assertContains($houseboy->id, $config['position_ids']);
        $this->assertContains($loader->id, $config['position_ids']);
        $this->assertContains($owwa->code, $config['status_codes']);
        $this->assertContains('Branch', $config['sources']);
        $this->assertTrue($config['enable_firstimer']);
    }
}
