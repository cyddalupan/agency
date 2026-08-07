<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ApplicantOma;
use App\Models\ApplicantOwWa;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS "Personal Information" — PI: 3. Certifications tab (TDD).
 *
 * The Certifications tab must render OMA and OWWA sections with all
 * checklist fields wired to the existing sub-store routes, and persisted
 * records must display in their sub-lists.
 */
class PersonalInformationCertificationsTabTest extends TestCase
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
            'agency_id'    => $this->agency->id,
            'has_passport' => 'with',
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
    public function certifications_tab_renders_oma_section_with_fields(): void
    {
        $html = $this->getShowHtml();
        $this->assertStringContainsString('Certifications', $html);
        $this->assertStringContainsString('OMA', $html);
        $this->assertStringContainsString('From', $html);
        $this->assertStringContainsString('To', $html);
        $this->assertStringContainsString('OMA Released', $html);
    }

    #[Test]
    public function certifications_tab_renders_owwa_section_with_fields(): void
    {
        $html = $this->getShowHtml();
        $this->assertStringContainsString('OWWA', $html);
        $this->assertStringContainsString('OWWA Released', $html);
        $this->assertStringContainsString('Local Flight', $html);
    }

    #[Test]
    public function oma_can_be_stored_and_is_listed(): void
    {
        $this->actingAs($this->user)->post(
            route('applicants.sub.store', [$this->applicant, 'oma']),
            ['from_date' => '2026-01-01', 'to_date' => '2026-12-31', 'released_date' => '2026-06-15']
        )->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('applicant_omas', [
            'applicant_id' => $this->applicant->id,
            'from_date'    => '2026-01-01 00:00:00',
            'to_date'      => '2026-12-31 00:00:00',
        ]);

        $html = $this->getShowHtml();
        $this->assertStringContainsString('Jan 01, 2026', $html);
    }

    #[Test]
    public function owWa_can_be_stored_and_is_listed(): void
    {
        $this->actingAs($this->user)->post(
            route('applicants.sub.store', [$this->applicant, 'owwa']),
            [
                'from_date'        => '2026-02-01',
                'to_date'          => '2026-11-30',
                'released_date'    => '2026-07-01',
                'local_flight_date' => '2026-07-20',
            ]
        )->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('applicant_owwas', [
            'applicant_id'      => $this->applicant->id,
            'from_date'         => '2026-02-01 00:00:00',
            'local_flight_date' => '2026-07-20 00:00:00',
        ]);

        $html = $this->getShowHtml();
        $this->assertStringContainsString('Feb 01, 2026', $html);
    }

    #[Test]
    public function stored_oma_and_owwa_seed_their_sublists_on_show(): void
    {
        ApplicantOma::create(['agency_id' => $this->agency->id, 'applicant_id' => $this->applicant->id, 'from_date' => '2026-03-01', 'to_date' => '2026-12-31', 'released_date' => '2026-09-01']);
        ApplicantOwWa::create(['agency_id' => $this->agency->id, 'applicant_id' => $this->applicant->id, 'from_date' => '2026-04-01', 'to_date' => '2026-11-30', 'released_date' => '2026-08-01', 'local_flight_date' => '2026-08-15']);

        $html = $this->getShowHtml();
        $this->assertStringContainsString('Mar 01, 2026', $html);
        $this->assertStringContainsString('Apr 01, 2026', $html);
    }
}
