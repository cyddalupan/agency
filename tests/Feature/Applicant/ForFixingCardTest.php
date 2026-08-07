<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Position;
use App\Models\StatusCode;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regression tests for the Mjolnir "For Fixing" Trello card (2026-08-03).
 *
 * Cyd's feedback: the Add Applicant form was missing data because positions
 * and statuses were filtered down to only the agency's newly-added options.
 * Expected: the FULL lists render. Contract / Contract Received are removed
 * from this form (they move to the tabbed Personal Information section).
 * Branch should render as a bigger (textarea) field.
 */
class ForFixingCardTest extends TestCase
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

        // Simulate the Gulf misconfiguration: only a couple of new options ticked.
        // Even with restrictive defaults, the full lists must render (see card).
        $settings = $this->agency->settings ?? [];
        $settings['applicant_form_defaults'] = [
            'position_ids'   => [45, 46],
            'status_codes'   => [45, 46, 47, 48, 49, 50, 51, 52],
            'sources'        => ['Facebook', 'Referral', 'Walk-in', 'Website', 'Other', 'Branch'],
            'enable_firstimer' => true,
            'firstimer_options' => ['Firstimer', 'Ex-Abroad'],
        ];
        $this->agency->update(['settings' => $settings]);
    }

    private function getCreateHtml(): string
    {
        return $this->actingAs($this->user)
            ->get(route('applicants.create'))
            ->assertOk()
            ->getContent();
    }

    #[Test]
    public function status_dropdown_shows_full_list_not_just_ticked_codes(): void
    {
        // Pick a common status well outside the agency's ticked 45-52 range.
        $common = StatusCode::where('label', 'Pending')
            ->orWhere('code', 1)
            ->orWhereNotIn('code', [45,46,47,48,49,50,51,52])
            ->first();
        $this->assertNotNull($common, 'Expected a common status code outside 45-52');

        $html = $this->getCreateHtml();

        // A standard status that was being hidden must now appear.
        $this->assertStringContainsString('>' . e($common->label) . '</option>', $html);
        // And the newly-added status is still available too.
        $owwa = StatusCode::where('label', 'For OWWA Make-Up Class')->first();
        if ($owwa) {
            $this->assertStringContainsString($owwa->label, $html);
        }
    }

    #[Test]
    public function preferred_position_dropdown_shows_full_list_not_just_ticked(): void
    {
        // A position that is NOT in the agency's ticked [45,46] set.
        $nurse = Position::create(['name' => 'Nurse']);

        $html = $this->getCreateHtml();
        $this->assertStringContainsString('>' . e($nurse->name) . '</option>', $html);
    }

    #[Test]
    public function contract_and_contract_received_are_removed_from_create_form(): void
    {
        $html = $this->getCreateHtml();

        $this->assertStringNotContainsString('name="contract"', $html);
        $this->assertStringNotContainsString('name="contract_received_date"', $html);
    }

    #[Test]
    public function branch_renders_as_dropdown_not_free_text_textarea(): void
    {
        $html = $this->getCreateHtml();

        // Branch must be the dropdown (single source of truth), never a free-text textarea.
        $this->assertStringContainsString('name="branch_id"', $html);
        $this->assertDoesNotMatchRegularExpression('/<textarea[^>]*name="branch"/', $html);
    }
}
