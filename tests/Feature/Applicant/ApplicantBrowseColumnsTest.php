<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantBrowseColumnsTest extends TestCase
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
            'name'      => 'Cyd Gulf',
        ]);
    }

    #[Test]
    public function applicant_can_be_created_with_branch_encoder_and_contract_fields(): void
    {
        $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'           => 'Andrea',
                'last_name'            => 'Reyes',
                'birthdate'            => '1995-06-15',
                'contact'              => '09171231234',
                'branch'               => 'Alabang Branch',
                'encoder'              => 'Cyd Gulf',
                'contract'             => UploadedFile::fake()->create('andrea_contract.pdf', 10, 'application/pdf'),
                'contract_received_date' => '2026-07-01',
            ]);

        $this->assertDatabaseHas('applicants', [
            'agency_id'             => $this->agency->id,
            'first_name'            => 'Andrea',
            'last_name'             => 'Reyes',
            'branch'                => 'Alabang Branch',
            'encoder'               => 'Cyd Gulf',
        ]);

        $applicant = Applicant::where('first_name', 'Andrea')->firstOrFail();
        $this->assertSame('2026-07-01', $applicant->contract_received_date?->format('Y-m-d'));
        $this->assertNotNull($applicant->contract);
        $this->assertStringStartsWith('contracts/', $applicant->contract);
    }

    #[Test]
    public function applicant_contract_upload_does_not_trigger_string_validation_error(): void
    {
        // Regression: uploading a contract file previously failed validation with
        // "The contract field must be a string" because the rule was nullable|string
        // but a file input submits an UploadedFile (array-like), not a string.
        Storage::fake('public');

        $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'           => 'Carlos',
                'last_name'            => 'Tan',
                'birthdate'            => '1990-03-12',
                'contact'              => '09175556666',
                'branch'               => 'Makati Branch',
                'encoder'              => 'Cyd Gulf',
                'contract'             => UploadedFile::fake()->create('signed_contract.pdf', 200, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('applicants', [
            'first_name' => 'Carlos',
            'last_name'  => 'Tan',
        ]);

        $applicant = Applicant::where('first_name', 'Carlos')->firstOrFail();
        $this->assertNotNull($applicant->contract);
        Storage::disk('public')->assertExists($applicant->contract);
    }

    #[Test]
    public function applicant_created_without_new_fields_has_them_nullable(): void
    {
        $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name' => 'Juan',
                'last_name'  => 'Dela Cruz',
            ]);

        // encoder is auto-derived; created_by = auth user (item 7).
        $this->assertDatabaseHas('applicants', [
            'first_name'           => 'Juan',
            'last_name'            => 'Dela Cruz',
            'branch'               => null,
            'contract'             => null,
            'contract_received_date' => null,
            'created_by'           => $this->user->id,
        ]);

        $a = \App\Models\Applicant::where('first_name', 'Juan')->first();
        $this->assertNotNull($a->encoder, 'encoder should be auto-derived on create');
    }

    #[Test]
    public function create_form_does_not_expose_encoder_input(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.create'));

        $response->assertOk();
        // Item 7: encoder is stored in DB but NOT visible to users as an input.
        $response->assertDontSee('name="encoder"', false);
    }

    #[Test]
    public function browse_index_displays_required_columns(): void
    {
        Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'first_name'   => 'Maria',
            'last_name'    => 'Santos',
            'birthdate'    => '1995-06-15',
            'contact'      => '09170000000',
            'branch'       => 'QC Branch',
            'encoder'      => 'Cyd Gulf',
            'contract'     => 'contracts/maria.pdf',
            'contract_received_date' => '2026-07-10',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertOk();
        // Header columns requested on the Trello card.
        foreach ([
            'Date Applied', 'Name', 'Status', 'Age', 'Contact#', 'Position',
            'Branch', 'Agent', 'Contract', 'Contract Received', 'Encoder', 'Action',
        ] as $col) {
            $response->assertSee($col);
        }

        // Row data visible.
        $response->assertSee('Maria');
        $response->assertSee('QC Branch');
        $response->assertSee('Cyd Gulf');
    }

    #[Test]
    public function applicant_age_is_derived_from_birthdate(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'birthdate' => now()->subYears(27)->format('Y-m-d'),
        ]);

        $this->assertSame(27, $applicant->age);
    }
}
