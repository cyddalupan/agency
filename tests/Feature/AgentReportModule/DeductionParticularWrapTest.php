<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b00a8e888a25dcbf0552080 — "DEDUCTION Particular clipped on Deduction tab".
 *
 * Cyd 2026-11-22: "wrap up text to display the complete Particular /
 * Description of the transaction." The particular cell used
 * `max-w-[16rem] truncate`, so anything past ~16rem was invisible unless you
 * hovered for the native title tooltip — which is useless on touch.
 *
 * The assertion is on the COMPLETE string, not a prefix, and on the cell's
 * classes, so re-adding `truncate` fails this test even though `truncate` is
 * CSS and Blade still emits the whole string into the HTML.
 *
 * The particular below is the real one on agent_deductions.id = 1255
 * (agency 7, agent 1115, ₱15,000.00), measured at 145 characters; 145 is also
 * the longest particular in the whole 1,024-row table.
 */
class DeductionParticularWrapTest extends TestCase
{
    use RefreshDatabase;

    private const LONG_PARTICULAR = 'KRISTILE ANNJOY DE SESTO ELITE REPAT | 08-11-2025 TO 20-03-2026 | TOOK MORE THAN 3 MONTHS BEFORE GOING BACK TO PHILIPPINES DUE TO MIDDLE EAST WAR';

    private Agency $agency;
    private Branch $branch;
    private User $admin;
    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $this->admin  = User::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'user_type' => 'admin',
            'status'    => 'active',
        ]);
        $this->agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'name'      => 'KRISTILE ANNJOY DE SESTO',
        ]);
    }

    private function makeDeduction(string $particular): AgentDeduction
    {
        return AgentDeduction::create([
            'agency_id'  => $this->agency->id,
            'user_id'    => $this->admin->id,
            'agent_id'   => $this->agent->id,
            'date'       => '2026-03-21',
            'account'    => AgentDeduction::ACCOUNT_DEDUCTION,
            'amount'     => 15000.00,
            'particular' => $particular,
        ]);
    }

    /** The exact <td> that carries the particular. */
    private function particularCell(string $html): string
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>' . $html);
        libxml_clear_errors();

        foreach ($dom->getElementsByTagName('td') as $td) {
            if (str_contains($td->textContent, self::LONG_PARTICULAR)) {
                return $td->ownerDocument->saveHTML($td);
            }
        }

        $this->fail('No table cell carries the long particular.');
    }

    #[Test]
    public function the_deductions_tab_renders_the_complete_long_particular(): void
    {
        $this->assertSame(145, strlen(self::LONG_PARTICULAR), 'Fixture drifted from the measured 145-char row 1255.');
        $this->makeDeduction(self::LONG_PARTICULAR);

        $response = $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'deductions']));

        $response->assertOk();

        $cell = $this->particularCell($response->getContent());

        // Full text, not a prefix. Whitelisted characters render verbatim.
        $this->assertStringContainsString(
            self::LONG_PARTICULAR,
            $cell,
            'The complete particular must be rendered in the cell.'
        );
    }

    #[Test]
    public function the_particular_cell_wraps_instead_of_truncating(): void
    {
        $this->makeDeduction(self::LONG_PARTICULAR);

        $response = $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'deductions']));
        $cell = $this->particularCell($response->getContent());

        $this->assertStringNotContainsString(
            'truncate',
            $cell,
            'The particular cell must not truncate; a truncate regression would hide text past 16rem.'
        );
        $this->assertStringNotContainsString('text-ellipsis', $cell);
        $this->assertStringNotContainsString('overflow-hidden', $cell);
        $this->assertStringContainsString('whitespace-normal', $cell, 'Text must be allowed to wrap.');
        $this->assertStringContainsString('break-words', $cell, 'Long words must break rather than overflow.');

        // Redundant affordance kept.
        $this->assertStringContainsString('title=', $cell);
    }

    #[Test]
    public function a_blank_particular_still_renders_an_em_dash(): void
    {
        AgentDeduction::create([
            'agency_id'  => $this->agency->id,
            'user_id'    => $this->admin->id,
            'agent_id'   => $this->agent->id,
            'date'       => '2026-03-21',
            'account'    => AgentDeduction::ACCOUNT_DEDUCTION,
            'amount'     => 500.00,
            'particular' => null,
        ]);

        $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'deductions']))
            ->assertOk()
            ->assertSee('—');
    }

    #[Test]
    public function the_amount_column_stays_right_aligned_and_nowrap(): void
    {
        $this->makeDeduction(self::LONG_PARTICULAR);

        $response = $this->actingAs($this->admin)
            ->get(route('agent_report.index', ['tab' => 'deductions']));
        $html = $response->getContent();

        $this->assertMatchesRegularExpression(
            '/<td class="[^"]*\btext-right\b[^"]*\bwhitespace-nowrap\b[^"]*">₱15,000\.00<\/td>/',
            $html,
            'The amount must stay right-aligned and unbroken when the row grows.'
        );
    }
}
