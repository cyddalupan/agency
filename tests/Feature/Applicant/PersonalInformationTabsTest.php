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
 * LANDAS "Personal Information" — 6-tab layout (TDD).
 *
 * The show page must render the Personal Information as six tabs:
 * Basic Information (default), Requirements, Certifications, Documents,
 * Upload Files, Status — per the Trello card.
 */
class PersonalInformationTabsTest extends TestCase
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
    public function show_page_renders_six_personal_information_tabs(): void
    {
        $html = $this->getShowHtml();

        foreach (['Basic Information', 'Requirements', 'Certifications', 'Documents', 'Upload Files', 'Status'] as $tab) {
            $this->assertStringContainsString($tab, $html, "Tab '{$tab}' should be present");
        }
    }

    #[Test]
    public function basic_information_is_the_default_active_tab(): void
    {
        $html = $this->getShowHtml();
        // The Basic Information tab button is marked tab-active by default.
        $this->assertMatchesRegularExpression('/data-pi-tab="basic"[^>]*tab-active|tab-active[^>]*data-pi-tab="basic"/', $html);
        // And its panel is visible (does NOT carry the hidden class).
        $this->assertMatchesRegularExpression('/data-pi-panel="basic"/', $html);
        $this->assertDoesNotMatchRegularExpression('/data-pi-panel="basic"[^>]*class="[^"]*\bhidden\b/', $html);
    }

    #[Test]
    public function existing_sub_entities_are_available_inside_tabs(): void
    {
        $html = $this->getShowHtml();
        // Passport lives under Requirements tab; Education under Basic Information.
        $this->assertStringContainsString('Passport', $html);
        $this->assertStringContainsString('Education', $html);
    }
}
