<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\Complaint;
use App\Models\ControlledDocument;
use App\Models\CorrectiveAction;
use App\Models\InternalAudit;
use App\Models\NonConformity;
use App\Models\Risk;
use App\Models\User;
use App\Services\QmsMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * QMS-20 (card 6afa0db1de9f235c9653f26f).
 *
 * The card's acceptance is precise: each tile's number equals the count of the
 * list it links to. So most of this file does exactly one thing — click the
 * tile's URL like a user would, parse the count rendered on that page, and
 * compare it to the number rendered on the tile. If the definitions in
 * QmsMetrics ever drift from the lists, these fail.
 *
 * It also guards the spec §17 rule that shapes everything else here: no sample
 * values dressed as live results, and every tile carries a period and an
 * "as of" stamp.
 */
class QmsDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->manager = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'qms_manager',
        ]);

        app()->instance('tenant_agency', $this->agency);
        Carbon::setTestNow(Carbon::parse('2026-11-16 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ---------------------------------------------------------------------
    // The card's acceptance: tile number == linked list count
    // ---------------------------------------------------------------------

    public function test_every_tiles_number_equals_the_count_of_the_list_it_links_to(): void
    {
        $this->seedOneOfEverything();

        $tiles = app(QmsMetrics::class)->tiles();

        $this->assertNotEmpty($tiles);

        foreach ($tiles as $tile) {
            $listResponse = $this->actingAs($this->manager)->get($tile['url']);

            $this->assertSame(
                200,
                $listResponse->status(),
                "Tile '{$tile['key']}' links to {$tile['url']}, which did not load."
            );

            $listCount = $this->countOnListPage($listResponse->getContent(), $tile);

            $this->assertNotNull(
                $listCount,
                "Tile '{$tile['key']}' links to a list that does not render a count."
            );

            $this->assertSame(
                $listCount,
                $tile['count'],
                "Tile '{$tile['key']}' says {$tile['count']} but its list says {$listCount}."
            );
        }
    }

    public function test_the_dashboard_renders_the_same_number_as_the_metrics(): void
    {
        $this->seedOneOfEverything();

        $response = $this->actingAs($this->manager)->get(route('qms.dashboard'))->assertOk();

        foreach (app(QmsMetrics::class)->tiles() as $tile) {
            // The count is rendered both as the visible number and as a
            // data attribute; both must agree with the service.
            $this->assertStringContainsString(
                'data-count="' . $tile['count'] . '"',
                $response->getContent(),
                "The dashboard did not render {$tile['count']} for '{$tile['key']}'."
            );
            $this->assertStringContainsString('id="tile-' . $tile['key'] . '"', $response->getContent());
        }
    }

    // ---------------------------------------------------------------------
    // The numbers themselves are right
    // ---------------------------------------------------------------------

    public function test_open_non_conformities_counts_unclosed_only(): void
    {
        $this->makeNc('NC-1', 'open', '2026-11-01');
        $this->makeNc('NC-2', 'closed', '2026-11-01');

        $this->assertSame(1, app(QmsMetrics::class)->openNonConformities()->count());
    }

    public function test_overdue_non_conformities_excludes_closed_and_future(): void
    {
        $this->makeNc('NC-LATE', 'open', '2026-11-10');
        $this->makeNc('NC-FUTURE', 'open', '2026-12-01');
        $this->makeNc('NC-DONE', 'closed', '2026-11-01');

        $this->assertSame(1, app(QmsMetrics::class)->overdueNonConformities()->count());
    }

    public function test_overdue_corrective_actions_excludes_completed(): void
    {
        $nc = $this->makeNc('NC-1', 'open', '2026-12-01');

        CorrectiveAction::create([
            'agency_id' => $this->agency->id, 'non_conformity_id' => $nc->id,
            'action_text' => 'Late one', 'due_date' => '2026-11-01', 'status' => 'open',
        ]);
        CorrectiveAction::create([
            'agency_id' => $this->agency->id, 'non_conformity_id' => $nc->id,
            'action_text' => 'Done one', 'due_date' => '2026-11-01',
            'completed_date' => '2026-11-05', 'status' => 'complete',
        ]);

        $this->assertSame(1, app(QmsMetrics::class)->overdueCorrectiveActions()->count());
    }

    public function test_risks_by_level_excludes_closed(): void
    {
        $this->makeRisk('RISK-1', Risk::LEVEL_HIGH, Risk::STATUS_OPEN);
        $this->makeRisk('RISK-2', Risk::LEVEL_HIGH, Risk::STATUS_CLOSED);
        $this->makeRisk('RISK-3', Risk::LEVEL_LOW, Risk::STATUS_OPEN);

        $this->assertSame(1, app(QmsMetrics::class)->risksByLevel(Risk::LEVEL_HIGH)->count());
    }

    public function test_overdue_complaints_matches_the_complaints_overdue_tab(): void
    {
        Complaint::create([
            'agency_id' => $this->agency->id, 'ref_no' => 'CMP-1', 'complainant_name' => 'A',
            'category' => 'Billing', 'description' => 'x', 'date_received' => '2026-11-01',
            'target_ack_date' => '2026-11-05', 'status' => Complaint::STATUS_OPEN,
        ]);
        Complaint::create([
            'agency_id' => $this->agency->id, 'ref_no' => 'CMP-2', 'complainant_name' => 'B',
            'category' => 'Billing', 'description' => 'x', 'date_received' => '2026-11-01',
            'target_ack_date' => '2026-11-05', 'status' => Complaint::STATUS_RESOLVED,
        ]);

        $this->assertSame(1, app(QmsMetrics::class)->overdueComplaints()->count());
    }

    public function test_documents_due_excludes_superseded(): void
    {
        ControlledDocument::create([
            'agency_id' => $this->agency->id, 'code' => 'DOC-1', 'title' => 'Live',
            'status' => ControlledDocument::STATUS_APPROVED, 'next_review_date' => '2026-11-20',
        ]);
        ControlledDocument::create([
            'agency_id' => $this->agency->id, 'code' => 'DOC-2', 'title' => 'Dead',
            'status' => ControlledDocument::STATUS_SUPERSEDED, 'next_review_date' => '2026-11-20',
        ]);

        $this->assertSame(1, app(QmsMetrics::class)->documentsDueForReview()->count());
    }

    public function test_upcoming_audits_excludes_conducted(): void
    {
        InternalAudit::create([
            'agency_id' => $this->agency->id, 'ref_no' => 'AUD-1', 'title' => 'Soon',
            'scheduled_date' => '2026-11-20', 'status' => InternalAudit::STATUS_PLANNED,
        ]);
        InternalAudit::create([
            'agency_id' => $this->agency->id, 'ref_no' => 'AUD-2', 'title' => 'Done',
            'scheduled_date' => '2026-11-20', 'conducted_date' => '2026-11-15',
            'status' => InternalAudit::STATUS_COMPLETED,
        ]);

        $this->assertSame(1, app(QmsMetrics::class)->upcomingAudits()->count());
    }

    // ---------------------------------------------------------------------
    // Traceability rules from the spec
    // ---------------------------------------------------------------------

    public function test_every_tile_carries_a_period_and_an_as_of_stamp(): void
    {
        foreach (app(QmsMetrics::class)->tiles() as $tile) {
            $this->assertNotEmpty($tile['period'], "Tile '{$tile['key']}' has no period.");
            $this->assertNotNull($tile['as_of'], "Tile '{$tile['key']}' has no as-of stamp.");
            $this->assertNotEmpty($tile['url'], "Tile '{$tile['key']}' links nowhere.");
        }
    }

    public function test_the_dashboard_stamps_as_of_and_the_period(): void
    {
        $this->actingAs($this->manager)
            ->get(route('qms.dashboard'))
            ->assertOk()
            ->assertSee('16 Nov 2026 10:00')
            ->assertSee('November 2026');
    }

    public function test_there_is_no_percentage_kpi_with_a_target(): void
    {
        $html = $this->actingAs($this->manager)->get(route('qms.dashboard'))->assertOk()->getContent();

        // Judge only what this card renders. The shared layout legitimately
        // contains an inline `height: 100%`, and inherited chrome is not ours
        // to police — the rule is about tiles not claiming percentages or
        // targets.
        if (preg_match('/<div[^>]*id="qms-tiles".*?<\/div>\s*<div class="alert/s', $html, $m)) {
            $html = $m[0];
        }

        $visible = preg_replace('/\s+/', ' ', strip_tags($html));

        $this->assertStringNotContainsString('%', $visible, 'A tile is showing a percentage.');

        // Not "the word target" — complaints legitimately have target dates.
        // The rule is that no tile presents a KPI target or a percentage of one.
        $this->assertDoesNotMatchRegularExpression(
            '/(\d+\s*%|of\s+target|against\s+target|target\s*[:=]\s*\d)/i',
            $visible,
            'A tile is presenting a percentage or a target KPI.'
        );
    }

    public function test_accounting_cannot_see_the_qms_dashboard(): void
    {
        $billing = User::factory()->create([
            'agency_id' => $this->agency->id, 'user_type' => 'billing',
        ]);

        $this->actingAs($billing)->get(route('qms.dashboard'))->assertForbidden();
    }

    public function test_an_internal_auditor_can_see_the_qms_dashboard(): void
    {
        $auditor = User::factory()->create([
            'agency_id' => $this->agency->id, 'user_type' => 'internal_auditor',
        ]);

        $this->actingAs($auditor)->get(route('qms.dashboard'))->assertOk();
    }

    public function test_an_empty_dashboard_reads_zero_not_a_sample_value(): void
    {
        $response = $this->actingAs($this->manager)->get(route('qms.dashboard'))->assertOk();

        foreach (app(QmsMetrics::class)->tiles() as $tile) {
            $this->assertSame(0, $tile['count'], "Tile '{$tile['key']}' invented a number on an empty database.");
        }

        $this->assertStringContainsString('data-count="0"', $response->getContent());
    }

    // ---------------------------------------------------------------------

    /**
     * Pull the rendered count from a list page.
     *
     * The three list screens built for this card render `#metric-count`; the
     * existing NC / risk / complaint registers render a tab badge with
     * `data-count`, so each is read the way that page actually shows it.
     */
    private function countOnListPage(string $html, array $tile): ?int
    {
        if (preg_match('/id="metric-count"[^>]*>\s*([\d,]+)\s*</', $html, $m)) {
            return (int) str_replace(',', '', $m[1]);
        }

        // The NC and complaint registers render every tab's count on every
        // page, so read the tab THIS tile's URL asks for — not the first one
        // in the markup, which would silently be "open" while the tile means
        // "overdue".
        $query = [];
        parse_str((string) parse_url($tile['url'], PHP_URL_QUERY), $query);
        $tab = $query['tab'] ?? $query['status'] ?? null;

        if ($tab !== null && preg_match('/id="tab-' . preg_quote($tab, '/') . '"[^>]*data-count="(\d+)"/', $html, $m)) {
            return (int) $m[1];
        }

        // Risk register: it renders the filtered total as #risk-count.
        if (str_starts_with($tile['key'], 'risks_')
            && preg_match('/id="risk-count"[^>]*>\s*([\d,]+)\s*</', $html, $m)) {
            return (int) str_replace(',', '', $m[1]);
        }

        return null;
    }

    private function seedOneOfEverything(): void
    {
        // One overdue NC, one live NC, one closed NC.
        $this->makeNc('NC-LATE', 'open', '2026-11-05');
        $this->makeNc('NC-LIVE', 'open', '2026-12-20');
        $this->makeNc('NC-DONE', 'closed', '2026-11-01');

        $nc = NonConformity::where('ref_no', 'NC-LATE')->first();

        CorrectiveAction::create([
            'agency_id' => $this->agency->id, 'non_conformity_id' => $nc->id,
            'action_text' => 'Overdue action', 'due_date' => '2026-11-01', 'status' => 'open',
        ]);

        $this->makeRisk('RISK-H', Risk::LEVEL_HIGH, Risk::STATUS_OPEN);
        $this->makeRisk('RISK-C', Risk::LEVEL_CRITICAL, Risk::STATUS_OPEN);

        Complaint::create([
            'agency_id' => $this->agency->id, 'ref_no' => 'CMP-1', 'complainant_name' => 'A',
            'category' => 'Billing', 'description' => 'late', 'date_received' => '2026-11-01',
            'target_ack_date' => '2026-11-04', 'status' => Complaint::STATUS_OPEN,
        ]);

        ControlledDocument::create([
            'agency_id' => $this->agency->id, 'code' => 'DOC-1', 'title' => 'Review me',
            'status' => ControlledDocument::STATUS_APPROVED, 'next_review_date' => '2026-11-25',
        ]);

        InternalAudit::create([
            'agency_id' => $this->agency->id, 'ref_no' => 'AUD-1', 'title' => 'Soon',
            'scheduled_date' => '2026-11-24', 'status' => InternalAudit::STATUS_PLANNED,
        ]);
    }

    private function makeNc(string $ref, string $status, string $due): NonConformity
    {
        $nc = NonConformity::create([
            'agency_id'     => $this->agency->id,
            'ref_no'        => $ref,
            'source'        => NonConformity::SOURCE_AUDIT,
            'detected_date' => '2026-11-01',
            'description'   => 'Reconciliation not signed.',
            'severity'      => NonConformity::SEVERITY_MINOR,
            'status'        => NonConformity::STATUS_OPEN,
            'due_date'      => $due,
        ]);

        if ($status === 'closed') {
            // Past the closure gate via the query builder: this test is about
            // counting, and the gate intentionally forbids opening straight
            // into `closed`.
            \Illuminate\Support\Facades\DB::table('non_conformities')
                ->where('id', $nc->id)
                ->update(['status' => NonConformity::STATUS_CLOSED]);
            $nc->refresh();
        }

        return $nc;
    }

    /**
     * Risk::saving() recomputes score and level from the axes, so the level
     * cannot be set directly — that is the model being correct, not a
     * limitation. Pick likelihood x impact that lands in the wanted band:
     * high is 12-19, critical is 20-25, low is 1-4.
     */
    private function makeRisk(string $ref, string $level, string $status): Risk
    {
        [$likelihood, $impact] = match ($level) {
            Risk::LEVEL_CRITICAL => [5, 5],   // 25
            Risk::LEVEL_HIGH     => [4, 4],   // 16
            Risk::LEVEL_MEDIUM   => [2, 3],   // 6
            default              => [1, 1],   // 1
        };

        return Risk::create([
            'agency_id'  => $this->agency->id,
            'ref_no'     => $ref,
            'title'      => 'Risk ' . $ref,
            'status'     => $status,
            'likelihood' => $likelihood,
            'impact'     => $impact,
        ]);
    }
}
