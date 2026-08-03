<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS "Personal Information" — PI:8 Data & schema prerequisites (TDD).
 *
 * Verifies the missing schema tables (spouse, family, emergency contacts,
 * NBI, OEC, visa, contract, ticket) can be created through the model AND
 * stored via the applicants.sub.store route, and that the Applicant model
 * exposes the new relations.
 */
class PersonalInformationSchemaTest extends TestCase
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

    /**
     * Case data: [type, field-key/value pairs to fill into the sub.store route]
     */
    public static function schemaCases(): array
    {
        return [
            'spouse' => [
                'type' => 'spouse',
                'data' => ['partner_name' => 'Maria Santos', 'number_of_children' => 2],
            ],
            'family' => [
                'type' => 'family',
                'data' => ['name' => 'Juan Dela Cruz', 'relation' => 'Father', 'occupation' => 'Farmer'],
            ],
            'emergency' => [
                'type' => 'emergency',
                'data' => ['name' => 'Ana Reyes', 'relationship' => 'Mother', 'contact' => '09171234567'],
            ],
            'nbi' => [
                'type' => 'nbi',
                'data' => ['nbi_no' => 'NBI-2026-0001', 'issue_date' => '2026-01-01', 'expiry_date' => '2026-12-31'],
            ],
            'oec' => [
                'type' => 'oec',
                'data' => ['oec_no' => 'OEC-0001', 'oec_release' => '2026-02-01'],
            ],
            'visa' => [
                'type' => 'visa',
                'data' => ['visa_no' => 'VISA-01', 'visa_type' => 'work', 'received_date' => '2026-03-01', 'stamped_date' => '2026-03-05', 'expiry_date' => '2027-03-01', 'approved_musaned' => 'yes'],
            ],
            'contract' => [
                'type' => 'contract',
                'data' => ['rfp' => 'RFP-001', 'sponsor' => 'Al Farid Co', 'sponsor_id' => 'SP-9', 'contact' => '021234567', 'address' => 'Riyadh', 'contract_received' => '2026-04-01', 'contract_signed' => '2026-04-10'],
            ],
            'ticket' => [
                'type' => 'ticket',
                'data' => ['airline' => 'Saudia', 'flight_date' => '2026-05-01', 'flight_time' => '10:30', 'flight_remarks' => 'Economy'],
            ],
        ];
    }

    #[Test]
    public function applicant_exposes_new_personal_information_relations(): void
    {
        // Assert the relation methods exist and return the expected Eloquent relation types.
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->applicant->spouse());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->applicant->family());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->applicant->emergencyContacts());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->applicant->nbi());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->applicant->oec());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->applicant->visa());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->applicant->contract());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->applicant->tickets());
    }

    #[Test]
    public function spellcheck_relations_are_loaded_with_applicant(): void
    {
        $loaded = $this->applicant->load([
            'spouse', 'family', 'emergencyContacts', 'nbi', 'oec', 'visa', 'contract', 'tickets',
        ]);
        foreach (['spouse', 'family', 'emergencyContacts', 'nbi', 'oec', 'visa', 'contract', 'tickets'] as $rel) {
            $this->assertNotNull($loaded->getRelation($rel), "Relation '{$rel}' should be loadable");
        }
    }

    #[Test]
    #[DataProvider('schemaCases')]
    public function new_records_can_be_stored_through_sub_store_route(string $type, array $data): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, $type]), $data);

        $response->assertRedirect(route('applicants.show', $this->applicant));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas($this->tableFor($type), [
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicant->id,
        ]);
    }

    #[Test]
    public function records_are_visible_through_the_applicant_relations(): void
    {
        /** @var \App\Models\ApplicantSpouse $spouse */
        $spouse = $this->applicant->spouse()->create([
            'agency_id'         => $this->agency->id,
            'partner_name'      => 'Maria Santos',
            'number_of_children' => 3,
        ]);

        /** @var \App\Models\ApplicantEmergencyContact $ec */
        $ec = $this->applicant->emergencyContacts()->create([
            'agency_id'    => $this->agency->id,
            'name'         => 'Ana Reyes',
            'relationship' => 'Mother',
            'contact'      => '09171234567',
        ]);

        $this->assertSame(1, $this->applicant->spouse()->count());
        $this->assertSame(1, $this->applicant->emergencyContacts()->count());
        $this->assertSame('Maria Santos', $spouse->partner_name);
        $this->assertSame('Ana Reyes', $ec->name);
    }

    private function tableFor(string $type): string
    {
        return match ($type) {
            'spouse'    => 'applicant_spouses',
            'family'    => 'applicant_family_members',
            'emergency' => 'applicant_emergency_contacts',
            'nbi'       => 'applicant_nbis',
            'oec'       => 'applicant_oecs',
            'visa'      => 'applicant_visas',
            'contract'  => 'applicant_contracts',
            'ticket'    => 'applicant_tickets',
            default     => throw new \InvalidArgumentException("Unknown type: {$type}"),
        };
    }
}
