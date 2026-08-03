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
 * LANDAS "Personal Information" — PI:1 Basic Information tab: Contact Numbers (TDD).
 *
 * The Basic Information tab must support MULTIPLE contact numbers per
 * applicant (hasMany, Add toggle), captured via applicant_contacts, stored
 * through the sub.store route, and listed back in the tab.
 */
class PersonalInformationContactNumbersTest extends TestCase
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
    public function basic_tab_renders_contact_numbers_section(): void
    {
        $this->assertStringContainsString('Contact Number', $this->getShowHtml());
    }

    #[Test]
    public function multiple_contact_numbers_are_supported_and_listed(): void
    {
        $this->applicant->contacts()->create(['agency_id' => $this->agency->id, 'contact' => '09171234567']);
        $this->applicant->contacts()->create(['agency_id' => $this->agency->id, 'contact' => '09179876543']);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('09171234567', $html);
        $this->assertStringContainsString('09179876543', $html);
    }

    #[Test]
    public function contact_number_can_be_stored_via_sub_store_route(): void
    {
        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'contacts']), [
                'contact' => '09175551234',
            ])
            ->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('applicant_contacts', [
            'applicant_id' => $this->applicant->id,
            'contact'      => '09175551234',
        ]);
    }
}
