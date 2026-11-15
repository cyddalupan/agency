<?php

namespace Tests\Feature\Receivable;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Card 6af82fef, Josh 2026-11-14:
 *   "Remove the notes from Receivable Transaction entry"
 *   "Remove the tabs on the Expenses transaction entry"
 */
class EntryPageChromeTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agency = Agency::factory()->create(['subdomain' => 'gulf', 'name' => 'GULF']);
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_the_receivable_entry_page_has_no_explanatory_notes_block(): void
    {
        $html = $this->actingAs($this->admin)->get(route('receivable.create'))->assertOk()->getContent();

        $this->assertStringNotContainsString('Applicant belongs to the selected', $html);
        $this->assertStringNotContainsString('Choose a Agent first', $html);
    }

    public function test_the_expense_entry_page_has_no_module_tabs(): void
    {
        $html = $this->actingAs($this->admin)->get(route('expense_request.create'))->assertOk()->getContent();

        $this->assertStringNotContainsString('Tab 1 · Receivable', $html);
        $this->assertStringNotContainsString('Tab 2 · Expenses', $html);
        // The dangling "Tab 2 —" subtitle went with the tabs.
        $this->assertStringNotContainsString('Tab 2 —', $html);
    }

    public function test_the_expense_entry_page_still_renders_the_form(): void
    {
        // Removing chrome must not remove the thing being entered.
        $html = $this->actingAs($this->admin)->get(route('expense_request.create'))->assertOk()->getContent();

        $this->assertStringContainsString('Save Request', $html);
        $this->assertStringContainsString('name="lines[0][amount]"', $html);
    }
}
