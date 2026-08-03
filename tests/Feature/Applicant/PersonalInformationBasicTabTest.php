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
 * LANDAS "Personal Information" — PI:1 Basic Information tab (TDD).
 *
 * The default Basic Information tab must render/capture Spouse Information
 * (Partner's Name, Number of Children), Family Information (Mother's Name/
 * Occupation, Father's Name/Occupation, Number of Siblings), In Case of
 * Emergency (Name, Relationship, Contact# — multiple entries allowed),
 * plus a Save Update button. Emergency contacts & contact numbers support
 * multiple entries (hasMany + Add toggle.
 */
class PersonalInformationBasicTabTest extends TestCase
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
    public function basic_tab_renders_spouse_information_section(): void
    {
        $html = $this->getShowHtml();

        $this->assertStringContainsString('Spouse Information', $html);
        $this->assertStringContainsString("Partner's Name", $html);
        $this->assertStringContainsString('Number of Children', $html);
    }

    #[Test]
    public function basic_tab_renders_family_information_section(): void
    {
        $html = $this->getShowHtml();

        $this->assertStringContainsString('Family Information', $html);
        $this->assertStringContainsString("Mother's Name", $html);
        $this->assertStringContainsString("Mother's Occupation", $html);
        $this->assertStringContainsString("Father's Name", $html);
        $this->assertStringContainsString("Father's Occupation", $html);
        $this->assertStringContainsString('Number of Siblings', $html);
    }

    #[Test]
    public function basic_tab_renders_emergency_contact_section(): void
    {
        $html = $this->getShowHtml();

        $this->assertStringContainsString('In Case of Emergency', $html);
        $this->assertStringContainsString('Relationship', $html);
        $this->assertStringContainsString('Contact', $html);
    }

    #[Test]
    public function basic_tab_includes_save_update_button(): void
    {
        $this->assertStringContainsString('Save Update', $this->getShowHtml());
    }

    #[Test]
    public function multiple_emergency_contacts_are_supported_in_basic_tab(): void
    {
        // Create two emergency contacts for the applicant.
        $this->applicant->emergencyContacts()->create(['agency_id' => $this->agency->id, 'name' => 'Ana Reyes', 'relationship' => 'Mother', 'contact' => '09171234567']);
        $this->applicant->emergencyContacts()->create(['agency_id' => $this->agency->id, 'name' => 'Boy Cruz', 'relationship' => 'Father', 'contact' => '09179876543']);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        // Both emergency contact rows appear in the Basic tab list.
        $this->assertStringContainsString('Ana Reyes', $html);
        $this->assertStringContainsString('Boy Cruz', $html);
        $this->assertStringContainsString('09171234567', $html);
        $this->assertStringContainsString('09179876543', $html);
    }

    #[Test]
    public function spouse_and_family_records_are_listed_in_basic_tab(): void
    {
        $this->applicant->spouse()->create(['agency_id' => $this->agency->id, 'partner_name' => 'Maria Santos', 'number_of_children' => 2]);
        $this->applicant->family()->create(['agency_id' => $this->agency->id, 'name' => 'Rosa Dela Cruz', 'relation' => 'Mother', 'occupation' => 'Teacher']);
        $this->applicant->family()->create(['agency_id' => $this->agency->id, 'name' => 'Juan Dela Cruz', 'relation' => 'Father', 'occupation' => 'Farmer']);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Maria Santos', $html);
        $this->assertStringContainsString('Rosa Dela Cruz', $html);
        $this->assertStringContainsString('Juan Dela Cruz', $html);
    }
}
