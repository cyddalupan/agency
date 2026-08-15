<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Create page agent cascade (requested via Toybits):
 * - the agent dropdown label is "Agent Name" (not "Agent (branch-scoped)")
 * - the Applicant dropdown only shows applicants under the selected agent:
 *   options carry data-agent, and the page JS hides non-matching options
 *   on agent change AND on initial load (so validation re-renders keep the filter).
 */
class ExpenseRequestCreateAgentFilterTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function createPageHtml(): string
    {
        return $this->actingAs($this->admin)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->getContent();
    }

    #[Test]
    public function agent_dropdown_is_labeled_agent_name(): void
    {
        $html = $this->createPageHtml();

        $this->assertStringContainsString('Agent Name', $html);
        $this->assertStringNotContainsString('Agent (branch-scoped)', $html);
    }

    #[Test]
    public function applicant_options_carry_their_agent_for_filtering(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branch->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);

        $html = $this->createPageHtml();

        $this->assertStringContainsString('data-agent="' . $agent->id . '"', $html);
        $this->assertStringContainsString(
            $applicant->last_name . ', ' . $applicant->first_name,
            $html
        );
    }

    #[Test]
    public function page_js_hides_applicants_not_under_the_selected_agent(): void
    {
        $html = $this->createPageHtml();

        // A named filter exists and hides (not just disables) non-matching options.
        $this->assertStringContainsString('filterApplicantsByAgent', $html);
        $this->assertStringContainsString("o.style.display = visible ? '' : 'none'", $html);
    }

    #[Test]
    public function applicant_filter_runs_on_agent_change_and_on_initial_load(): void
    {
        $html = $this->createPageHtml();

        // Agent change re-filters the line's applicant dropdown.
        $this->assertStringContainsString("name.includes('[agent_id]')", $html);

        // The filter is also applied to every line on initial load
        // (so a validation-error re-render keeps non-matching applicants hidden).
        $this->assertMatchesRegularExpression('/Initial state.*filterApplicantsByAgent/s', $html);
    }

    #[Test]
    public function agent_and_applicant_dropdowns_are_present_per_line(): void
    {
        $html = $this->createPageHtml();

        $this->assertStringContainsString('lines[0][agent_id]', $html);
        $this->assertStringContainsString('lines[0][applicant_id]', $html);
    }
}
