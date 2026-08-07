<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ApplicantContract;
use App\Models\ApplicantOec;
use App\Models\ApplicantTicket;
use App\Models\ApplicantVisa;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS "Personal Information" — PI: 4. Documents tab (TDD).
 *
 * The Documents tab must render OEC, VISA, Contract and Ticket data-entry
 * sections with all checklist fields, wired to the existing sub-store routes,
 * and persisted records must display in their sub-lists.
 */
class PersonalInformationDocumentsTabTest extends TestCase
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
    public function documents_tab_renders_oec_section_with_fields(): void
    {
        $html = $this->getShowHtml();
        $this->assertStringContainsString('OEC', $html);
        $this->assertStringContainsString('OEC No.', $html);
        $this->assertStringContainsString('OEC Release', $html);
    }

    #[Test]
    public function documents_tab_renders_visa_section_with_fields(): void
    {
        $html = $this->getShowHtml();
        $this->assertStringContainsString('VISA', $html);
        $this->assertStringContainsString('Visa No.', $html);
        $this->assertStringContainsString('Received', $html);
        $this->assertStringContainsString('Stamped', $html);
        $this->assertStringContainsString('Visa Expiry', $html);
        $this->assertStringContainsString('Approved Musaned', $html);
    }

    #[Test]
    public function documents_tab_renders_contract_section_with_fields(): void
    {
        $html = $this->getShowHtml();
        $this->assertStringContainsString('Contract', $html);
        $this->assertStringContainsString('RFP', $html);
        $this->assertStringContainsString('Sponsor', $html);
        $this->assertStringContainsString('Contract Received', $html);
        $this->assertStringContainsString('Contract Signed', $html);
    }

    #[Test]
    public function documents_tab_renders_ticket_section_with_fields(): void
    {
        $html = $this->getShowHtml();
        $this->assertStringContainsString('Ticket', $html);
        $this->assertStringContainsString('Airline', $html);
        $this->assertStringContainsString('Flight Date', $html);
        $this->assertStringContainsString('Flight Time', $html);
        $this->assertStringContainsString('Flight Remarks', $html);
    }

    #[Test]
    public function oec_can_be_stored_and_is_listed(): void
    {
        $this->actingAs($this->user)->post(
            route('applicants.sub.store', [$this->applicant, 'oec']),
            ['oec_no' => 'OEC-7777', 'oec_release' => '2026-02-01']
        )->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('applicant_oecs', [
            'applicant_id' => $this->applicant->id,
            'oec_no'       => 'OEC-7777',
        ]);

        $html = $this->getShowHtml();
        $this->assertStringContainsString('OEC-7777', $html);
    }

    #[Test]
    public function visa_can_be_stored_and_is_listed(): void
    {
        $this->actingAs($this->user)->post(
            route('applicants.sub.store', [$this->applicant, 'visa']),
            [
                'visa_no'          => 'VISA-123',
                'visa_type'        => 'work',
                'received_date'    => '2026-03-01',
                'stamped_date'     => '2026-03-05',
                'expiry_date'      => '2027-03-01',
                'approved_musaned' => 'yes',
            ]
        )->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('applicant_visas', [
            'applicant_id' => $this->applicant->id,
            'visa_no'      => 'VISA-123',
        ]);

        $html = $this->getShowHtml();
        $this->assertStringContainsString('VISA-123', $html);
    }

    #[Test]
    public function contract_can_be_stored_and_is_listed(): void
    {
        $this->actingAs($this->user)->post(
            route('applicants.sub.store', [$this->applicant, 'contract']),
            [
                'rfp'               => 'RFP-001',
                'sponsor'           => 'Al Farid Co',
                'sponsor_id'        => 'SP-9',
                'contact'           => '021234567',
                'address'           => 'Riyadh',
                'contract_received' => '2026-04-01',
                'contract_signed'   => '2026-04-10',
            ]
        )->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('applicant_contracts', [
            'applicant_id' => $this->applicant->id,
            'rfp'          => 'RFP-001',
        ]);

        $html = $this->getShowHtml();
        $this->assertStringContainsString('RFP-001', $html);
    }

    #[Test]
    public function ticket_can_be_stored_and_is_listed(): void
    {
        $this->actingAs($this->user)->post(
            route('applicants.sub.store', [$this->applicant, 'ticket']),
            [
                'airline'        => 'Saudia',
                'flight_date'    => '2026-05-01',
                'flight_time'    => '10:30',
                'flight_remarks' => 'Direct',
            ]
        )->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('applicant_tickets', [
            'applicant_id' => $this->applicant->id,
            'airline'      => 'Saudia',
        ]);

        $html = $this->getShowHtml();
        $this->assertStringContainsString('Saudia', $html);
    }

    #[Test]
    public function stored_records_seed_their_sublists_on_show(): void
    {
        ApplicantOec::create(['agency_id' => $this->agency->id, 'applicant_id' => $this->applicant->id, 'oec_no' => 'OEC-555', 'oec_release' => '2026-01-01']);
        ApplicantVisa::create(['agency_id' => $this->agency->id, 'applicant_id' => $this->applicant->id, 'visa_no' => 'VISA-555']);
        ApplicantContract::create(['agency_id' => $this->agency->id, 'applicant_id' => $this->applicant->id, 'rfp' => 'RFP-555']);
        ApplicantTicket::create(['agency_id' => $this->agency->id, 'applicant_id' => $this->applicant->id, 'airline' => 'Emirates', 'flight_date' => '2026-06-01']);

        $html = $this->getShowHtml();
        $this->assertStringContainsString('OEC-555', $html);
        $this->assertStringContainsString('VISA-555', $html);
        $this->assertStringContainsString('RFP-555', $html);
        $this->assertStringContainsString('Emirates', $html);
    }
}
