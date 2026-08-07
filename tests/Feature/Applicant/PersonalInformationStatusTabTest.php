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
 * LANDAS "Personal Information" — PI: 6. Status tab (TDD).
 *
 * The Status tab must let an admin set, per applicant:
 *  - Applicant# (optional)
 *  - Applicant Status: FRA (dropdown), Status (dropdown), Status Date (date)
 *  - Repat Status: Repat (tick box), Repat Date (date)
 *  - Save Status (button) persisting all of the above.
 */
class PersonalInformationStatusTabTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);
        $this->seed(\Database\Seeders\StatusTransitionSeeder::class);

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

    private function getShowHtml(): string
    {
        return $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();
    }

    #[Test]
    public function status_tab_renders_all_pi6_fields(): void
    {
        $html = $this->getShowHtml();

        // Status tab panel + button
        $this->assertStringContainsString('data-pi-panel="status"', $html);
        $this->assertStringContainsString('Save Status', $html);

        // Applicant# (optional)
        $this->assertStringContainsString('Applicant#', $html);
        $this->assertStringContainsString('name="applicant_no"', $html);

        // Applicant Status: FRA dropdown
        $this->assertStringContainsString('name="fra"', $html);

        // Status dropdown (status_code) seeded options visible
        $this->assertStringContainsString('name="status_code"', $html);
        $this->assertStringContainsString('Pending', $html);
        $this->assertStringContainsString('Deployed', $html);

        // Status Date
        $this->assertStringContainsString('name="status_date"', $html);

        // Repat tick box
        $this->assertStringContainsString('name="repat"', $html);
        $this->assertStringContainsString('type="checkbox"', $html);

        // Repat Date
        $this->assertStringContainsString('name="repat_date"', $html);

        // Form posts to the status route
        $this->assertStringContainsString(route('applicants.status', $this->applicant), $html);
    }

    #[Test]
    public function save_status_persists_all_pi6_fields(): void
    {
        $response = $this->actingAs($this->user)->patch(
            route('applicants.status', $this->applicant),
            [
                'applicant_no' => 'LN-2026-0042',
                'fra'          => 'for_fra',
                'status_code'  => 1, // For Interview (valid from Pending)
                'status_date'  => '2026-08-01',
                'repat'        => '1',
                'repat_date'   => '2026-08-02',
            ]
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('applicants', [
            'id'           => $this->applicant->id,
            'applicant_no' => 'LN-2026-0042',
            'fra'          => 'for_fra',
            'status_code'  => 1,
            'status_date'  => '2026-08-01 00:00:00',
            'repat'        => 1,
            'repat_date'   => '2026-08-02 00:00:00',
        ]);
    }

    #[Test]
    public function applicant_number_is_optional(): void
    {
        $response = $this->actingAs($this->user)->patch(
            route('applicants.status', $this->applicant),
            [
                'status_code' => 0,
                'repat'       => '0',
            ]
        );

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'id'          => $this->applicant->id,
            'status_code' => 0,
            'repat'       => 0,
        ]);
    }

    #[Test]
    public function fra_dropdown_rejects_invalid_value(): void
    {
        $response = $this->actingAs($this->user)->patch(
            route('applicants.status', $this->applicant),
            [
                'status_code' => 0,
                'fra'         => 'not-a-valid-fra-value',
            ]
        );

        $response->assertSessionHasErrors('fra');
    }

    #[Test]
    public function repat_tickbox_persists_boolean_true_and_false(): void
    {
        // ticked
        $this->actingAs($this->user)->patch(
            route('applicants.status', $this->applicant),
            ['status_code' => 0, 'repat' => '1', 'repat_date' => '2026-08-03']
        );
        $this->assertDatabaseHas('applicants', ['id' => $this->applicant->id, 'repat' => 1]);

        // unchecked
        $this->actingAs($this->user)->patch(
            route('applicants.status', $this->applicant),
            ['status_code' => 0, 'repat' => '0']
        );
        $this->assertDatabaseHas('applicants', ['id' => $this->applicant->id, 'repat' => 0]);
    }
}
