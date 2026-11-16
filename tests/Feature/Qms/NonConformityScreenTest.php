<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\CorrectiveAction;
use App\Models\NonConformity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-16 (card 6afa0dad2729295c62765c76) — non-conformity and CAPA screens.
 *
 * Acceptance from the card: closing with an unverified action is refused and
 * shows why; the happy path closes cleanly.
 */
class NonConformityScreenTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->withQms()->create();
        app()->instance('tenant_agency', $this->agency);
        $this->manager = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'qms_manager',
        ]);
    }

    private function userWithType(string $type): User
    {
        return User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => $type]);
    }

    private function makeNc(array $overrides = []): NonConformity
    {
        return NonConformity::withoutGlobalScopes()->create(array_merge([
            'agency_id'     => $this->agency->id,
            'ref_no'        => 'NC-' . random_int(100, 999),
            'source'        => NonConformity::SOURCE_AUDIT,
            'detected_date' => now()->toDateString(),
            'description'   => 'Monthly fee reconciliation was not performed.',
            'severity'      => NonConformity::SEVERITY_MAJOR,
            'status'        => NonConformity::STATUS_OPEN,
        ], $overrides));
    }

    private function makeAction(NonConformity $nc, array $overrides = []): CorrectiveAction
    {
        return CorrectiveAction::withoutGlobalScopes()->create(array_merge([
            'agency_id'         => $this->agency->id,
            'non_conformity_id' => $nc->id,
            'action_text'       => 'Introduce a monthly reconciliation checklist.',
        ], $overrides));
    }

    private function closeProperly(NonConformity $nc): NonConformity
    {
        $this->makeAction($nc, [
            'verified_at'               => now(),
            'effectiveness_verified_at' => now(),
        ]);

        $nc->update(['status' => NonConformity::STATUS_CLOSED]);

        return $nc->fresh();
    }

    // -----------------------------------------------------------------
    // Acceptance: refusal shows why
    // -----------------------------------------------------------------

    #[Test]
    public function closing_with_an_unverified_action_is_refused_and_says_why(): void
    {
        $nc = $this->makeNc(['ref_no' => 'NC-REFUSE']);
        $this->makeAction($nc, ['action_text' => 'Issue the checklist']);

        $this->actingAs($this->manager)
            ->from(route('non_conformities.show', $nc))
            ->post(route('non_conformities.close', $nc))
            ->assertRedirect(route('non_conformities.show', $nc))
            ->assertSessionHas('error', function ($message) {
                return str_contains($message, 'cannot be closed yet')
                    && str_contains($message, 'has not been verified')
                    && str_contains($message, 'effectiveness reviewed');
            });

        $this->assertSame(NonConformity::STATUS_OPEN, $nc->fresh()->status);
    }

    #[Test]
    public function the_detail_screen_explains_why_closure_is_blocked(): void
    {
        $nc = $this->makeNc(['ref_no' => 'NC-EXPLAIN']);
        $this->makeAction($nc);

        $response = $this->actingAs($this->manager)
            ->get(route('non_conformities.show', $nc))
            ->assertOk();

        $response->assertSee('closure-blocked', false);
        $response->assertSee('This cannot be closed yet.');
        $response->assertSee('has not been verified');
        $response->assertSee('effectiveness reviewed');
        $response->assertSee('btn-disabled', false);
    }

    #[Test]
    public function the_happy_path_closes_cleanly(): void
    {
        $nc = $this->makeNc(['ref_no' => 'NC-CLEAN']);

        $this->makeAction($nc, [
            'action_text'               => 'Issue the checklist',
            'completed_date'            => now()->toDateString(),
            'verified_at'               => now(),
            'verification_notes'        => 'Checklist issued and in use.',
            'effectiveness_verified_at' => now(),
            'effectiveness_notes'       => 'Two months, no missed reconciliation.',
        ]);

        // The screen now offers an enabled button and no blockers.
        $this->actingAs($this->manager)
            ->get(route('non_conformities.show', $nc))
            ->assertOk()
            ->assertSee('closure-ready', false)
            ->assertDontSee('closure-blocked', false);

        $this->actingAs($this->manager)
            ->post(route('non_conformities.close', $nc))
            ->assertRedirect(route('non_conformities.show', $nc))
            ->assertSessionHas('success');

        $closed = $nc->fresh();
        $this->assertSame(NonConformity::STATUS_CLOSED, $closed->status);
        $this->assertNotNull($closed->closed_at);
    }

    #[Test]
    public function closing_with_no_actions_at_all_is_refused(): void
    {
        $nc = $this->makeNc();

        $this->actingAs($this->manager)
            ->post(route('non_conformities.close', $nc))
            ->assertSessionHas('error');

        $this->assertSame(NonConformity::STATUS_OPEN, $nc->fresh()->status);
    }

    #[Test]
    public function a_status_edit_cannot_smuggle_a_closure_past_the_gate(): void
    {
        // The update form has a status dropdown; choosing Closed there must hit
        // the same gate, not slip through as a plain field update.
        $nc = $this->makeNc();
        $this->makeAction($nc);

        $this->actingAs($this->manager)
            ->put(route('non_conformities.update', $nc), [
                'status'   => NonConformity::STATUS_CLOSED,
                'severity' => $nc->severity,
            ])
            ->assertSessionHas('error');

        $this->assertSame(NonConformity::STATUS_OPEN, $nc->fresh()->status);
    }

    // -----------------------------------------------------------------
    // The register
    // -----------------------------------------------------------------

    #[Test]
    public function the_register_lists_findings_with_severity_and_status(): void
    {
        $this->makeNc(['ref_no' => 'NC-LIST', 'severity' => NonConformity::SEVERITY_CRITICAL]);

        $this->actingAs($this->manager)
            ->get(route('non_conformities.index'))
            ->assertOk()
            ->assertSee('NC-LIST')
            ->assertSee('Critical')
            ->assertSee('Open');
    }

    #[Test]
    public function an_empty_register_says_so(): void
    {
        $this->actingAs($this->manager)
            ->get(route('non_conformities.index'))
            ->assertOk()
            ->assertSee('nc-empty', false);
    }

    #[Test]
    public function overdue_findings_are_highlighted(): void
    {
        $late = $this->makeNc([
            'ref_no'   => 'NC-LATE',
            'due_date' => now()->subDays(3)->toDateString(),
        ]);

        $this->actingAs($this->manager)
            ->get(route('non_conformities.index'))
            ->assertOk()
            ->assertSee('nc-overdue-' . $late->id, false);

        $this->actingAs($this->manager)
            ->get(route('non_conformities.show', $late))
            ->assertOk()
            ->assertSee('badge-overdue', false);
    }

    #[Test]
    public function the_overdue_tab_excludes_closed_findings(): void
    {
        // A closed finding is built by closing it properly: creating one
        // already-closed would (correctly) be refused by the gate.
        $this->closeProperly($this->makeNc([
            'ref_no'   => 'NC-CLOSED-LATE',
            'due_date' => now()->subDays(9)->toDateString(),
        ]));

        $this->actingAs($this->manager)
            ->get(route('non_conformities.index', ['tab' => 'overdue']))
            ->assertOk()
            ->assertSee('nc-empty', false);
    }

    // -----------------------------------------------------------------
    // Intake and the narrative fields
    // -----------------------------------------------------------------

    #[Test]
    public function a_finding_can_be_recorded_through_the_form(): void
    {
        $this->actingAs($this->manager)
            ->post(route('non_conformities.store'), [
                'ref_no'       => 'NC-FORM',
                'source'       => NonConformity::SOURCE_COMPLAINT,
                'detected_date' => now()->toDateString(),
                'description'  => 'Applicant documents were released without an authorisation signature.',
                'severity'     => NonConformity::SEVERITY_MAJOR,
                'process_area' => 'Document release',
            ])
            ->assertRedirect();

        $nc = NonConformity::withoutGlobalScopes()->where('ref_no', 'NC-FORM')->firstOrFail();
        $this->assertSame(NonConformity::STATUS_OPEN, $nc->status);
        $this->assertSame('Document release', $nc->process_area);
    }

    #[Test]
    public function containment_and_root_cause_are_saved_from_the_detail_screen(): void
    {
        $nc = $this->makeNc();

        $this->actingAs($this->manager)
            ->put(route('non_conformities.update', $nc), [
                'containment' => 'Reconciliation frozen until the checklist exists.',
                'root_cause'  => 'No named owner for the monthly step.',
                'severity'    => $nc->severity,
                'status'      => NonConformity::STATUS_ROOT_CAUSE,
            ])
            ->assertRedirect(route('non_conformities.show', $nc));

        $fresh = $nc->fresh();
        $this->assertStringContainsString('frozen', (string) $fresh->containment);
        $this->assertStringContainsString('named owner', (string) $fresh->root_cause);
        $this->assertSame(NonConformity::STATUS_ROOT_CAUSE, $fresh->status);
    }

    #[Test]
    public function the_detail_screen_prompts_for_missing_containment_and_root_cause(): void
    {
        $nc = $this->makeNc();

        $this->actingAs($this->manager)
            ->get(route('non_conformities.show', $nc))
            ->assertOk()
            ->assertSee('nc-containment', false)
            ->assertSee('nc-root-cause', false)
            ->assertSee('Not recorded yet');
    }

    // -----------------------------------------------------------------
    // Corrective actions
    // -----------------------------------------------------------------

    #[Test]
    public function a_corrective_action_can_be_added(): void
    {
        $nc = $this->makeNc();

        $this->actingAs($this->manager)
            ->post(route('non_conformities.actions.store', $nc), [
                'action_text' => 'Assign a named owner to the monthly reconciliation.',
            ])
            ->assertRedirect(route('non_conformities.show', $nc));

        $this->assertCount(1, $nc->fresh()->correctiveActions);
    }

    #[Test]
    public function verification_and_effectiveness_are_recorded_in_order(): void
    {
        $nc = $this->makeNc();
        $action = $this->makeAction($nc);

        // Effectiveness before verification is refused.
        $this->actingAs($this->manager)
            ->put(route('non_conformities.actions.update', [$nc, $action]), ['effectiveness' => 1])
            ->assertSessionHas('error');

        $this->assertNull($action->fresh()->effectiveness_verified_at);

        // Complete, then verify, then effectiveness.
        $this->actingAs($this->manager)
            ->put(route('non_conformities.actions.update', [$nc, $action]), ['complete' => 1]);

        $this->actingAs($this->manager)
            ->put(route('non_conformities.actions.update', [$nc, $action]), ['verify' => 1]);

        $this->actingAs($this->manager)
            ->put(route('non_conformities.actions.update', [$nc, $action]), ['effectiveness' => 1]);

        $fresh = $action->fresh();
        $this->assertNotNull($fresh->completed_date);
        $this->assertNotNull($fresh->verified_at);
        $this->assertNotNull($fresh->effectiveness_verified_at);
        $this->assertSame(CorrectiveAction::STATUS_EFFECTIVE, $fresh->status);
    }

    #[Test]
    public function verifying_before_completing_is_refused(): void
    {
        $nc = $this->makeNc();
        $action = $this->makeAction($nc);

        $this->actingAs($this->manager)
            ->put(route('non_conformities.actions.update', [$nc, $action]), ['verify' => 1])
            ->assertSessionHas('error');

        $this->assertNull($action->fresh()->verified_at);
    }

    #[Test]
    public function an_action_from_another_non_conformity_is_not_found(): void
    {
        $mine = $this->makeNc();
        $other = $this->makeNc();
        $foreignAction = $this->makeAction($other);

        $this->actingAs($this->manager)
            ->put(route('non_conformities.actions.update', [$mine, $foreignAction]), ['complete' => 1])
            ->assertNotFound();
    }

    #[Test]
    public function a_closed_non_conformity_cannot_take_new_actions(): void
    {
        $nc = $this->closeProperly($this->makeNc());

        $this->actingAs($this->manager)
            ->post(route('non_conformities.actions.store', $nc), ['action_text' => 'Too late'])
            ->assertForbidden();
    }

    #[Test]
    public function removing_an_action_takes_it_out_of_the_gate(): void
    {
        $nc = $this->makeNc();

        $this->makeAction($nc, [
            'verified_at'               => now(),
            'effectiveness_verified_at' => now(),
        ]);
        $mistake = $this->makeAction($nc, ['action_text' => 'Added in error']);

        $this->assertFalse($nc->fresh()->can_close);

        $this->actingAs($this->manager)
            ->delete(route('non_conformities.actions.destroy', [$nc, $mistake]))
            ->assertRedirect(route('non_conformities.show', $nc));

        $this->assertTrue($nc->fresh()->can_close);
    }

    // -----------------------------------------------------------------
    // Roles and tenancy
    // -----------------------------------------------------------------

    #[Test]
    public function an_accounting_user_cannot_reach_non_conformities(): void
    {
        $this->actingAs($this->userWithType('billing'))
            ->get(route('non_conformities.index'))
            ->assertForbidden();
    }

    #[Test]
    public function an_internal_auditor_reads_but_cannot_sign_off(): void
    {
        $auditor = $this->userWithType('internal_auditor');
        $nc = $this->makeNc();
        $action = $this->makeAction($nc);

        $this->actingAs($auditor)->get(route('non_conformities.index'))->assertOk();
        $this->actingAs($auditor)->get(route('non_conformities.show', $nc))->assertOk();

        $this->actingAs($auditor)->get(route('non_conformities.create'))->assertForbidden();
        $this->actingAs($auditor)->post(route('non_conformities.close', $nc))->assertForbidden();
        $this->actingAs($auditor)
            ->put(route('non_conformities.actions.update', [$nc, $action]), ['verify' => 1])
            ->assertForbidden();

        $this->assertNull($action->fresh()->verified_at);
    }

    #[Test]
    public function another_agencys_finding_is_out_of_reach(): void
    {
        $otherAgency = Agency::factory()->create();
        $foreign = NonConformity::withoutGlobalScopes()->create([
            'agency_id'     => $otherAgency->id,
            'ref_no'        => 'NC-FOREIGN',
            'source'        => NonConformity::SOURCE_AUDIT,
            'detected_date' => now()->toDateString(),
            'description'   => 'Not yours',
            'status'        => NonConformity::STATUS_OPEN,
        ]);

        $this->actingAs($this->manager)
            ->get(route('non_conformities.show', $foreign))
            ->assertNotFound();
    }

    #[Test]
    public function a_qms_manager_sees_the_sidebar_link(): void
    {
        $this->actingAs($this->manager)
            ->get(route('non_conformities.index'))
            ->assertOk()
            ->assertSee(route('non_conformities.index'), false);
    }
}
