<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\Risk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-11 (card 6afa0da82ced12ad55cfdf0a) — risk register spine.
 *
 * Acceptance from the card: score/level banding at the boundaries,
 * 1×1=low, 5×5=critical, 3×4=12→high.
 */
class RiskTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->withQms()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->user = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'admin']);
    }

    /** A risk with only the columns the schema requires. */
    private function makeRisk(array $overrides = []): Risk
    {
        return Risk::withoutGlobalScopes()->create(array_merge([
            'agency_id'  => $this->agency->id,
            'ref_no'     => 'RISK-001',
            'title'      => 'Unverified applicant documents',
            'likelihood' => 3,
            'impact'     => 4,
            'status'     => Risk::STATUS_OPEN,
            'created_by' => $this->user->id,
        ], $overrides));
    }

    // -----------------------------------------------------------------
    // Acceptance: the banding boundaries the card names
    // -----------------------------------------------------------------

    #[Test]
    public function one_by_one_is_low_and_five_by_five_is_critical(): void
    {
        $this->assertSame(1, Risk::scoreFor(1, 1));
        $this->assertSame(Risk::LEVEL_LOW, Risk::levelForPair(1, 1));

        $this->assertSame(25, Risk::scoreFor(5, 5));
        $this->assertSame(Risk::LEVEL_CRITICAL, Risk::levelForPair(5, 5));
    }

    #[Test]
    public function three_by_four_scores_twelve_and_is_high(): void
    {
        $this->assertSame(12, Risk::scoreFor(3, 4));
        $this->assertSame(Risk::LEVEL_HIGH, Risk::levelForPair(3, 4));
    }

    /** Every one of the 25 cells, so no cell is silently unbanded. */
    #[Test]
    public function every_cell_of_the_five_by_five_matrix_bands_lowest_to_highest(): void
    {
        $order = [
            Risk::LEVEL_LOW      => 0,
            Risk::LEVEL_MEDIUM   => 1,
            Risk::LEVEL_HIGH     => 2,
            Risk::LEVEL_CRITICAL => 3,
        ];

        foreach (range(1, 5) as $likelihood) {
            foreach (range(1, 5) as $impact) {
                $level = Risk::levelForPair($likelihood, $impact);
                $this->assertArrayHasKey($level, $order, "no band for {$likelihood}x{$impact}");

                // Severity must never fall as either axis rises.
                foreach (range(1, 5) as $higherLikelihood) {
                    if ($higherLikelihood <= $likelihood) {
                        continue;
                    }
                    $higher = Risk::levelForPair($higherLikelihood, $impact);
                    $this->assertGreaterThanOrEqual(
                        $order[$level],
                        $order[$higher],
                        "{$higherLikelihood}x{$impact} must not be milder than {$likelihood}x{$impact}"
                    );
                }
            }
        }
    }

    #[Test]
    public function the_band_edges_are_exact(): void
    {
        // Low 1-4, Medium 5-11, High 12-19, Critical 20-25.
        $this->assertSame(Risk::LEVEL_LOW, Risk::levelFor(4));
        $this->assertSame(Risk::LEVEL_MEDIUM, Risk::levelFor(5));
        $this->assertSame(Risk::LEVEL_MEDIUM, Risk::levelFor(11));
        $this->assertSame(Risk::LEVEL_HIGH, Risk::levelFor(12));
        $this->assertSame(Risk::LEVEL_HIGH, Risk::levelFor(19));
        $this->assertSame(Risk::LEVEL_CRITICAL, Risk::levelFor(20));
        $this->assertSame(Risk::LEVEL_CRITICAL, Risk::levelFor(25));
    }

    #[Test]
    public function a_score_above_the_matrix_still_bands_critical_rather_than_falling_through(): void
    {
        $this->assertSame(Risk::LEVEL_CRITICAL, Risk::levelFor(30));
    }

    #[Test]
    public function a_zero_or_negative_score_bands_low(): void
    {
        $this->assertSame(Risk::LEVEL_LOW, Risk::levelFor(0));
        $this->assertSame(Risk::LEVEL_LOW, Risk::levelFor(-5));
    }

    // -----------------------------------------------------------------
    // The stored score is kept in step by the model
    // -----------------------------------------------------------------

    #[Test]
    public function saving_computes_the_score_and_level(): void
    {
        $risk = $this->makeRisk(['likelihood' => 3, 'impact' => 4]);

        $this->assertSame(12, $risk->risk_score);
        $this->assertSame(Risk::LEVEL_HIGH, $risk->risk_level);
    }

    #[Test]
    public function changing_the_axes_recomputes_the_stored_score(): void
    {
        $risk = $this->makeRisk(['likelihood' => 1, 'impact' => 1]);
        $this->assertSame(1, $risk->risk_score);

        $risk->update(['likelihood' => 5, 'impact' => 5]);
        $risk->refresh();

        $this->assertSame(25, $risk->risk_score);
        $this->assertSame(Risk::LEVEL_CRITICAL, $risk->risk_level);
    }

    /** A caller cannot smuggle in a score that disagrees with the axes. */
    #[Test]
    public function a_caller_supplied_score_is_overwritten(): void
    {
        $risk = $this->makeRisk(['likelihood' => 2, 'impact' => 2, 'risk_score' => 99, 'risk_level' => 'critical']);

        $this->assertSame(4, $risk->risk_score);
        $this->assertSame(Risk::LEVEL_LOW, $risk->risk_level);
    }

    #[Test]
    public function the_stored_columns_match_the_helper_for_a_sample_of_pairs(): void
    {
        foreach ([[1, 1], [2, 3], [3, 4], [4, 4], [5, 1], [5, 5]] as [$likelihood, $impact]) {
            $risk = $this->makeRisk([
                'ref_no'     => 'R-' . $likelihood . '-' . $impact,
                'likelihood' => $likelihood,
                'impact'     => $impact,
            ]);

            $this->assertSame($likelihood * $impact, $risk->risk_score);
            $this->assertSame(Risk::levelForPair($likelihood, $impact), $risk->risk_level);
        }
    }

    // -----------------------------------------------------------------
    // Register rules
    // -----------------------------------------------------------------

    #[Test]
    public function ref_no_is_unique_per_agency_but_reusable_across_agencies(): void
    {
        $this->makeRisk(['ref_no' => 'RISK-001']);

        $other = Agency::factory()->create();
        $second = Risk::withoutGlobalScopes()->create([
            'agency_id'  => $other->id,
            'ref_no'     => 'RISK-001',
            'title'      => 'Same reference, different agency',
            'likelihood' => 2,
            'impact'     => 2,
        ]);

        $this->assertNotNull($second->id);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->makeRisk(['ref_no' => 'RISK-001', 'title' => 'Duplicate in the same agency']);
    }

    #[Test]
    public function a_risk_is_scoped_to_its_tenant(): void
    {
        $this->makeRisk(['ref_no' => 'RISK-001']);

        $other = Agency::factory()->create();
        app()->instance('tenant_agency', $other);

        $this->assertSame(0, Risk::query()->count());
    }

    #[Test]
    public function a_closed_risk_is_not_review_overdue(): void
    {
        $overdue = $this->makeRisk([
            'ref_no'      => 'RISK-OLD',
            'review_date' => now()->subDay()->toDateString(),
            'status'      => Risk::STATUS_OPEN,
        ]);
        $this->assertTrue($overdue->is_review_overdue);

        $closed = $this->makeRisk([
            'ref_no'      => 'RISK-CLOSED',
            'review_date' => now()->subDay()->toDateString(),
            'status'      => Risk::STATUS_CLOSED,
        ]);
        $this->assertFalse($closed->is_review_overdue);
    }

    #[Test]
    public function writing_a_risk_records_an_audit_event(): void
    {
        $this->actingAs($this->user);
        $risk = $this->makeRisk();

        $this->assertDatabaseHas('audit_events', [
            'subject_type' => Risk::class,
            'subject_id'   => $risk->id,
            'subject_label' => 'RISK-001',
            'action'       => 'created',
        ]);
    }

    public static function levelLabelProvider(): array
    {
        return [
            ['low', 'Low'],
            ['medium', 'Medium'],
            ['high', 'High'],
            ['critical', 'Critical'],
        ];
    }

    #[Test]
    #[DataProvider('levelLabelProvider')]
    public function each_level_has_a_label_and_a_badge(string $level, string $label): void
    {
        // Drive the value in through the axes rather than setting risk_level.
        $pairs = ['low' => [1, 1], 'medium' => [2, 3], 'high' => [3, 4], 'critical' => [5, 5]];
        [$likelihood, $impact] = $pairs[$level];

        $risk = $this->makeRisk([
            'ref_no'     => 'R-' . $level,
            'likelihood' => $likelihood,
            'impact'     => $impact,
        ]);

        $this->assertSame($level, $risk->risk_level);
        $this->assertSame($label, $risk->level_label);
        $this->assertNotEmpty($risk->level_badge);
    }
}
