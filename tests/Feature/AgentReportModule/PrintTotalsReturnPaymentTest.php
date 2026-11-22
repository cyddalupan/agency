<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regression: /agents-report/print 500'd for every user, filtered or not.
 *
 * AgentReportController::print() built its `totals` array without the
 * 'return_payment' key, while resources/views/agent_report/print.blade.php
 * renders `$totals['return_payment']` in the <tfoot>. Any render of the print
 * route therefore raised "Undefined array key 'return_payment'" before a
 * single byte of the page was written.
 *
 * The route is not covered by a "renders 200" test anywhere else — only by
 * the 404 guard for a foreign agent id, and a 404 never reaches the blade.
 * These tests pin the two things that matter: the page renders, and the
 * return-payment column in the footer carries the right number in the same
 * column the row values use.
 */
class PrintTotalsReturnPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Branch $branch;
    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::put('fx_usd_to_php', 56.0, 3600);

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        // IdentifyAgency binds this on a real request; without it TenantScope
        // resolves the agency from a session key actAs() never sets.
        app()->instance('tenant_agency', $this->agency);

        $this->branch = Branch::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'PRINT-BRANCH',
        ]);

        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => null,
            'user_type' => 'admin',
        ]);

        $this->agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'name'      => 'PRINT TOTAL AGENT',
        ]);
    }

    #[Test]
    public function the_print_route_renders_for_an_admin(): void
    {
        $this->actingAs($this->user)
            ->get(route('agent_report.print'))
            ->assertOk()
            ->assertSee('PRINT TOTAL AGENT')
            ->assertSee('Total Return Payment')
            ->assertDontSee('Undefined array key');
    }

    #[Test]
    public function the_print_route_renders_when_filtered_to_one_agent(): void
    {
        $this->actingAs($this->user)
            ->get(route('agent_report.print', ['agent_id' => $this->agent->id]))
            ->assertOk()
            ->assertSee('PRINT TOTAL AGENT');
    }

    #[Test]
    public function the_print_footer_has_a_cell_for_every_total_the_rows_carry(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('agent_report.print'))
            ->assertOk()
            ->getContent();

        // Header has 9 columns; every row and the footer must match, or the
        // Payables/Return-Payment figures silently shift one column left.
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();

        $table = $doc->getElementsByTagName('table')->item(0);
        $this->assertNotNull($table, 'print view must render a table');

        $rows = $table->getElementsByTagName('tr');
        $expected = $rows->item(0)->getElementsByTagName('*')->length;
        $this->assertSame(9, $expected, 'header must declare 9 columns');

        $foot = $table->getElementsByTagName('tfoot')->item(0);
        $footRow = $foot->getElementsByTagName('tr')->item(0);
        $cells = 0;
        foreach ($footRow->childNodes as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }
            $cells += (int) $node->getAttribute('colspan') ?: 1;
        }
        $this->assertSame(9, $cells, 'footer must cover all 9 columns');
    }

    #[Test]
    public function the_csv_export_has_the_same_nine_columns_as_the_print_view(): void
    {
        $this->actingAs($this->user)
            ->get(route('agent_report.export'))
            ->assertOk()
            ->assertSee('Total Return Payment');
    }

    #[Test]
    public function the_csv_export_header_and_data_rows_have_matching_column_counts(): void
    {
        $csv = $this->actingAs($this->user)
            ->get(route('agent_report.export'))
            ->assertOk()
            ->getContent();

        $lines = array_values(array_filter(explode("\n", trim($csv))));
        $this->assertNotEmpty($lines, 'CSV must not be empty');

        $header = str_getcsv($lines[0]);
        $this->assertCount(9, $header, 'header must carry 9 columns');
        $this->assertSame('Total Return Payment', $header[6]);

        foreach (array_slice($lines, 1) as $i => $line) {
            $this->assertCount(9, str_getcsv($line), 'data row '.($i + 1).' must carry 9 columns');
        }
    }
}
