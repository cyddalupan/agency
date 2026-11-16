<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\Risk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-12 (card 6afa0da98260dda52823960e) — risk register screens + matrix.
 *
 * Acceptance from the card: create a risk, then see it in the list and in the
 * matrix at the right cell.
 */
class RiskScreenTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private User $auditor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->admin   = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'admin']);
        $this->auditor = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'internal_auditor']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'ref_no'      => 'RISK-001',
            'title'       => 'Applicant documents not verified',
            'process_area' => 'Applicant vetting',
            'cause'       => 'No second check',
            'consequence' => 'Undocumented applicant deployed',
            'likelihood'  => 3,
            'impact'      => 4,
            'status'      => Risk::STATUS_OPEN,
        ], $overrides);
    }

    private function makeRisk(array $overrides = []): Risk
    {
        return Risk::withoutGlobalScopes()->create(array_merge($this->payload(), [
            'agency_id' => $this->agency->id,
            'created_by' => $this->admin->id,
        ], $overrides));
    }

    // -----------------------------------------------------------------
    // Acceptance: create → appears in the list AND in the right matrix cell
    // -----------------------------------------------------------------

    #[Test]
    public function creating_a_risk_through_the_form_lists_it_and_places_it_in_the_matrix(): void
    {
        $this->actingAs($this->admin)
            ->post(route('risks.store'), $this->payload(['likelihood' => 3, 'impact' => 4]))
            ->assertRedirect(route('risks.index'));

        $risk = Risk::withoutGlobalScopes()->where('ref_no', 'RISK-001')->firstOrFail();
        $this->assertSame(12, $risk->risk_score);
        $this->assertSame(Risk::LEVEL_HIGH, $risk->risk_level);

        // In the list, with its score and level.
        $list = $this->actingAs($this->admin)->get(route('risks.index'));
        $list->assertOk();
        $list->assertSee('RISK-001');
        $list->assertSee('Applicant documents not verified');
        $list->assertSee('High');

        // In the matrix, in the 3x4 cell specifically.
        $matrix = $this->actingAs($this->admin)->get(route('risks.matrix'));
        $matrix->assertOk();
        $matrix->assertSee('RISK-001');

        $html = $matrix->getContent();
        $this->assertMatchesRegularExpression(
            '#id="cell-3-4"[^>]*data-count="1"#',
            $html,
            'the risk should occupy the 3x4 cell'
        );
        // ...and nowhere else.
        $this->assertSame(1, preg_match_all('#data-risk-ref="RISK-001"#', $html), 'the risk appears once in the matrix');
    }

    #[Test]
    public function the_matrix_cell_carries_the_banded_level_for_that_pair(): void
    {
        $this->makeRisk(['likelihood' => 3, 'impact' => 4]);

        $html = $this->actingAs($this->admin)->get(route('risks.matrix'))->getContent();

        $this->assertMatchesRegularExpression('#id="cell-3-4"[^>]*data-level="high"#', $html);
        // A neighbouring cell of a different band still reads its own level.
        $this->assertMatchesRegularExpression('#id="cell-1-1"[^>]*data-level="low"#', $html);
        $this->assertMatchesRegularExpression('#id="cell-5-5"[^>]*data-level="critical"#', $html);
    }

    #[Test]
    public function all_twenty_five_cells_are_drawn(): void
    {
        $html = $this->actingAs($this->admin)->get(route('risks.matrix'))->getContent();

        foreach (range(1, 5) as $l) {
            foreach (range(1, 5) as $i) {
                $this->assertStringContainsString("id=\"cell-{$l}-{$i}\"", $html, "cell {$l}x{$i} missing");
            }
        }
    }

    #[Test]
    public function two_risks_in_the_same_cell_both_appear(): void
    {
        $this->makeRisk(['ref_no' => 'RISK-001']);
        $this->makeRisk(['ref_no' => 'RISK-002', 'title' => 'Second risk']);

        $html = $this->actingAs($this->admin)->get(route('risks.matrix'))->getContent();

        $this->assertMatchesRegularExpression('#id="cell-3-4"[^>]*data-count="2"#', $html);
        $this->assertStringContainsString('data-risk-ref="RISK-001"', $html);
        $this->assertStringContainsString('data-risk-ref="RISK-002"', $html);
    }

    #[Test]
    public function closed_risks_are_left_out_of_the_matrix_but_stay_in_the_list(): void
    {
        $this->makeRisk(['ref_no' => 'RISK-OPEN']);
        $this->makeRisk(['ref_no' => 'RISK-DONE', 'status' => Risk::STATUS_CLOSED]);

        $matrix = $this->actingAs($this->admin)->get(route('risks.matrix'))->getContent();
        $this->assertStringContainsString('data-risk-ref="RISK-OPEN"', $matrix);
        $this->assertStringNotContainsString('data-risk-ref="RISK-DONE"', $matrix);

        $this->actingAs($this->admin)->get(route('risks.index'))->assertSee('RISK-DONE');
    }

    // -----------------------------------------------------------------
    // Sorting and filtering
    // -----------------------------------------------------------------

    #[Test]
    public function the_list_sorts_by_score_high_to_low_by_default(): void
    {
        $this->makeRisk(['ref_no' => 'LOW', 'likelihood' => 1, 'impact' => 1]);
        $this->makeRisk(['ref_no' => 'HIGH', 'likelihood' => 4, 'impact' => 4]);
        $this->makeRisk(['ref_no' => 'MID', 'likelihood' => 2, 'impact' => 3]);

        $html = $this->actingAs($this->admin)->get(route('risks.index'))->getContent();

        $this->assertLessThan(strpos($html, 'MID'), strpos($html, 'HIGH'));
        $this->assertLessThan(strpos($html, 'LOW'), strpos($html, 'MID'));
    }

    #[Test]
    public function the_list_can_be_sorted_low_to_high(): void
    {
        $this->makeRisk(['ref_no' => 'LOW', 'likelihood' => 1, 'impact' => 1]);
        $this->makeRisk(['ref_no' => 'HIGH', 'likelihood' => 4, 'impact' => 4]);

        $html = $this->actingAs($this->admin)->get(route('risks.index', ['sort' => 'score_asc']))->getContent();

        $this->assertLessThan(strpos($html, 'HIGH'), strpos($html, 'LOW'));
    }

    #[Test]
    public function the_list_filters_by_level_and_status(): void
    {
        $this->makeRisk(['ref_no' => 'LOW-1', 'likelihood' => 1, 'impact' => 1]);
        $this->makeRisk(['ref_no' => 'CRIT-1', 'likelihood' => 5, 'impact' => 5]);

        $byLevel = $this->actingAs($this->admin)->get(route('risks.index', ['level' => 'critical']));
        $byLevel->assertSee('CRIT-1');
        $byLevel->assertDontSee('LOW-1');

        $closed = $this->makeRisk(['ref_no' => 'CLOSED-1', 'status' => Risk::STATUS_CLOSED, 'likelihood' => 1, 'impact' => 1]);
        $byStatus = $this->actingAs($this->admin)->get(route('risks.index', ['status' => Risk::STATUS_CLOSED]));
        $byStatus->assertSee('CLOSED-1');
        $byStatus->assertDontSee('CRIT-1');
    }

    #[Test]
    public function the_list_searches_ref_title_and_process(): void
    {
        $this->makeRisk(['ref_no' => 'RISK-PAY', 'title' => 'Payroll error', 'process_area' => 'Payroll']);
        $this->makeRisk(['ref_no' => 'RISK-OTH', 'title' => 'Other thing', 'process_area' => 'Vetting']);

        $this->actingAs($this->admin)->get(route('risks.index', ['q' => 'payroll']))
            ->assertSee('RISK-PAY')
            ->assertDontSee('RISK-OTH');
    }

    #[Test]
    public function the_matrix_ignores_the_status_filter_and_shows_only_live_exposure(): void
    {
        $this->makeRisk(['ref_no' => 'LIVE', 'likelihood' => 2, 'impact' => 2]);
        $this->makeRisk(['ref_no' => 'GONE', 'status' => Risk::STATUS_CLOSED, 'likelihood' => 2, 'impact' => 2]);

        $html = $this->actingAs($this->admin)->get(route('risks.matrix'))->getContent();

        $this->assertMatchesRegularExpression('#id="cell-2-2"[^>]*data-count="1"#', $html);
    }

    // -----------------------------------------------------------------
    // Edit and delete
    // -----------------------------------------------------------------

    #[Test]
    public function editing_a_risk_recomputes_the_score_and_moves_it_to_a_new_cell(): void
    {
        $risk = $this->makeRisk(['likelihood' => 1, 'impact' => 1]);

        $this->actingAs($this->admin)
            ->put(route('risks.update', $risk), $this->payload(['likelihood' => 5, 'impact' => 5]))
            ->assertRedirect(route('risks.index'));

        $risk->refresh();
        $this->assertSame(25, $risk->risk_score);
        $this->assertSame(Risk::LEVEL_CRITICAL, $risk->risk_level);

        $html = $this->actingAs($this->admin)->get(route('risks.matrix'))->getContent();
        $this->assertMatchesRegularExpression('#id="cell-5-5"[^>]*data-count="1"#', $html);
        $this->assertMatchesRegularExpression('#id="cell-1-1"[^>]*data-count="0"#', $html);
    }

    #[Test]
    public function the_edit_form_is_prefilled_with_the_current_values(): void
    {
        $risk = $this->makeRisk(['likelihood' => 4, 'impact' => 2, 'title' => 'Distinct title']);

        $response = $this->actingAs($this->admin)->get(route('risks.edit', $risk));

        $response->assertOk();
        $response->assertSee('Distinct title');
        $response->assertSee('RISK-001');
    }

    #[Test]
    public function a_risk_can_be_deleted(): void
    {
        $risk = $this->makeRisk();

        $this->actingAs($this->admin)
            ->delete(route('risks.destroy', $risk))
            ->assertRedirect(route('risks.index'));

        $this->assertDatabaseMissing('risks', ['id' => $risk->id, 'deleted_at' => null]);

        // Assert on the table, not the whole page: the success flash itself
        // names the risk ("Risk RISK-001 deleted."), so a page-wide
        // assertDontSee would fail on the confirmation rather than on data.
        $html = $this->actingAs($this->admin)->get(route('risks.index'))->getContent();
        preg_match('#<tbody>(.*?)</tbody>#s', $html, $body);
        $this->assertStringNotContainsString('RISK-001', $body[1] ?? '');
    }

    // -----------------------------------------------------------------
    // Validation and the role gate
    // -----------------------------------------------------------------

    #[Test]
    public function the_reference_must_be_unique_within_the_agency(): void
    {
        $this->makeRisk(['ref_no' => 'RISK-001']);

        $this->actingAs($this->admin)
            ->post(route('risks.store'), $this->payload(['ref_no' => 'RISK-001']))
            ->assertSessionHasErrors('ref_no');

        $this->assertSame(1, Risk::withoutGlobalScopes()->where('ref_no', 'RISK-001')->count());
    }

    #[Test]
    public function likelihood_and_impact_must_be_within_one_to_five(): void
    {
        $this->actingAs($this->admin)
            ->post(route('risks.store'), $this->payload(['likelihood' => 6]))
            ->assertSessionHasErrors('likelihood');

        $this->actingAs($this->admin)
            ->post(route('risks.store'), $this->payload(['impact' => 0]))
            ->assertSessionHasErrors('impact');
    }

    #[Test]
    public function the_create_form_suggests_the_next_reference(): void
    {
        $this->makeRisk(['ref_no' => 'RISK-001']);

        $this->actingAs($this->admin)->get(route('risks.create'))
            ->assertOk()
            ->assertSee('RISK-002');
    }

    #[Test]
    public function the_internal_auditor_can_read_but_not_manage(): void
    {
        $risk = $this->makeRisk();

        $this->actingAs($this->auditor)->get(route('risks.index'))->assertOk();
        $this->actingAs($this->auditor)->get(route('risks.matrix'))->assertOk();
        $this->actingAs($this->auditor)->get(route('risks.create'))->assertForbidden();
        $this->actingAs($this->auditor)->post(route('risks.store'), $this->payload(['ref_no' => 'X-1']))->assertForbidden();
        $this->actingAs($this->auditor)->get(route('risks.edit', $risk))->assertForbidden();
        $this->actingAs($this->auditor)->delete(route('risks.destroy', $risk))->assertForbidden();
    }

    #[Test]
    public function a_staff_user_cannot_see_the_risk_register_at_all(): void
    {
        $staff = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'staff']);

        $this->actingAs($staff)->get(route('risks.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('risks.matrix'))->assertForbidden();
    }

    #[Test]
    public function the_sidebar_shows_the_risk_register_for_qms_roles_only(): void
    {
        $qms = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'qms_manager']);

        $this->assertTrue($qms->canAccessModule('risks'));
        $this->assertTrue($this->auditor->canAccessModule('risks'));
        $this->assertTrue($this->admin->canAccessModule('risks'));

        $staff = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'staff']);
        $this->assertFalse($staff->canAccessModule('risks'));
    }

    #[Test]
    public function an_empty_register_says_so(): void
    {
        $this->actingAs($this->admin)->get(route('risks.index'))
            ->assertOk()
            ->assertSee('No risks recorded yet.');
    }
}
