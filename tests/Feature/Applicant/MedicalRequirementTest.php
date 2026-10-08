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

/**
 * LANDAS "Personal Information > Requirements" — MEDICAL (TDD).
 *
 * Card "Add Applicant - MEDICAL": under the NBI section the Requirements tab
 * must show a MEDICAL block with:
 *   Clinic Name - Issue Date - Expire Date - Remarks - Upload Medical.
 * Records are stored via the sub.store route (type=medical) and can carry an
 * uploaded medical file.
 */
class MedicalRequirementTest extends TestCase
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
    public function requirements_tab_renders_medical_section_under_nbi(): void
    {
        $html = $this->getShowHtml();

        foreach (['MEDICAL', 'Clinic Name', 'Issue Date', 'Expire Date', 'Remarks', 'Upload Medical'] as $s) {
            $this->assertStringContainsString($s, $html, "'{$s}' should render in the Requirements tab");
        }

        // The MEDICAL block must sit after the NBI block.
        $nbiPos = strpos($html, 'NBI');
        $medPos = strpos($html, 'Clinic Name');
        $this->assertNotFalse($nbiPos);
        $this->assertNotFalse($medPos);
        $this->assertGreaterThan($nbiPos, $medPos, 'MEDICAL fields should appear after NBI');
    }

    #[Test]
    public function medical_can_be_stored_via_sub_store_route(): void
    {
        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'medical']), [
                'clinic_name' => 'ABC Medical Clinic',
                'issue_date'  => '2026-02-01',
                'expiry_date' => '2027-02-01',
                'remarks'     => 'Fit to work',
            ])
            ->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('applicant_medicals', [
            'applicant_id' => $this->applicant->id,
            'clinic_name'  => 'ABC Medical Clinic',
            'remarks'      => 'Fit to work',
        ]);

        $medical = $this->applicant->medical()->first();
        $this->assertNotNull($medical);
        $this->assertEquals('2026-02-01', $medical->issue_date?->format('Y-m-d'));
        $this->assertEquals('2027-02-01', $medical->expiry_date?->format('Y-m-d'));
    }

    #[Test]
    public function medical_can_upload_a_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('medical.jpg');

        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'medical']), [
                'clinic_name' => 'Health First Clinic',
                'issue_date'  => '2026-03-01',
                'expiry_date' => '2027-03-01',
                'file'        => $file,
            ])
            ->assertSessionHas('success');

        $medical = $this->applicant->medical()->first();
        $this->assertNotNull($medical);
        $this->assertNotNull($medical->file_path);
        Storage::disk('public')->assertExists($medical->file_path);
    }

    #[Test]
    public function medical_record_can_be_deleted(): void
    {
        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'medical']), [
                'clinic_name' => 'Delete Me Clinic',
            ]);

        $medical = $this->applicant->medical()->first();
        $this->assertNotNull($medical);

        $this->actingAs($this->user)
            ->delete(route('applicants.sub.destroy', [$this->applicant, 'medical', $medical->id]))
            ->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseMissing('applicant_medicals', ['id' => $medical->id]);
    }
}
