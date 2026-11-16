<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\CorrectiveAction;
use App\Models\NonConformity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-15 (card 6afa0dac333d35f77d709e61) — non-conformity and CAPA tables,
 * models and relations.
 *
 * Acceptance from the card: a non-conformity cannot reach `closed` while any
 * corrective action lacks both verification and effectiveness dates.
 */
class NonConformityTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->withQms()->create();
        app()->instance('tenant_agency', $this->agency);
    }

    private function makeNc(array $overrides = []): NonConformity
    {
        return NonConformity::withoutGlobalScopes()->create(array_merge([
            'agency_id'     => $this->agency->id,
            'ref_no'        => 'NC-' . random_int(100, 999),
            'source'        => NonConformity::SOURCE_AUDIT,
            'detected_date' => now()->toDateString(),
            'description'   => 'Placement fee reconciliation was not performed for the month.',
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

    // -----------------------------------------------------------------
    // Acceptance: the closure gate
    // -----------------------------------------------------------------

    #[Test]
    public function a_non_conformity_without_any_action_cannot_close(): void
    {
        $nc = $this->makeNc();

        $this->expectException(ValidationException::class);

        $nc->update(['status' => NonConformity::STATUS_CLOSED]);
    }

    #[Test]
    public function an_unverified_action_blocks_closure(): void
    {
        $nc = $this->makeNc();
        $this->makeAction($nc);

        try {
            $nc->update(['status' => NonConformity::STATUS_CLOSED]);
            $this->fail('Closing should have been refused.');
        } catch (ValidationException $e) {
            $messages = $e->errors()['status'] ?? [];
            $this->assertNotEmpty($messages);
            $this->assertStringContainsString('has not been verified', implode(' ', $messages));
        }

        $this->assertNotSame(NonConformity::STATUS_CLOSED, $nc->fresh()->status);
    }

    #[Test]
    public function a_verified_but_not_effectiveness_reviewed_action_blocks_closure(): void
    {
        $nc = $this->makeNc();
        $this->makeAction($nc, [
            'completed_date' => now()->toDateString(),
            'verified_at'    => now(),
        ]);

        try {
            $nc->update(['status' => NonConformity::STATUS_CLOSED]);
            $this->fail('Closing should have been refused: effectiveness was never reviewed.');
        } catch (ValidationException $e) {
            $messages = implode(' ', $e->errors()['status'] ?? []);
            $this->assertStringContainsString('effectiveness reviewed', $messages);
        }

        $this->assertNotSame(NonConformity::STATUS_CLOSED, $nc->fresh()->status);
    }

    #[Test]
    public function closing_succeeds_once_every_action_is_verified_and_effectiveness_reviewed(): void
    {
        $nc = $this->makeNc();
        $this->makeAction($nc, [
            'completed_date'            => now()->toDateString(),
            'verified_at'               => now(),
            'verification_notes'        => 'Checklist issued and used.',
            'effectiveness_verified_at' => now(),
            'effectiveness_notes'       => 'Two months with no missed reconciliation.',
        ]);

        $nc->update(['status' => NonConformity::STATUS_CLOSED]);

        $fresh = $nc->fresh();
        $this->assertSame(NonConformity::STATUS_CLOSED, $fresh->status);
        $this->assertNotNull($fresh->closed_at, 'closing stamps the time');
    }

    #[Test]
    public function every_action_must_be_signed_off_not_just_one(): void
    {
        $nc = $this->makeNc();

        $this->makeAction($nc, [
            'action_text'               => 'Checklist',
            'completed_date'            => now()->toDateString(),
            'verified_at'               => now(),
            'effectiveness_verified_at' => now(),
        ]);

        // The second action is still outstanding.
        $this->makeAction($nc, ['action_text' => 'Second action, untouched']);

        $this->expectException(ValidationException::class);

        $nc->update(['status' => NonConformity::STATUS_CLOSED]);
    }

    #[Test]
    public function the_gate_reports_every_reason_not_just_the_first(): void
    {
        $nc = $this->makeNc();
        $this->makeAction($nc, ['action_text' => 'A']);
        $this->makeAction($nc, ['action_text' => 'B']);

        try {
            $nc->update(['status' => NonConformity::STATUS_CLOSED]);
            $this->fail('Closing should have been refused.');
        } catch (ValidationException $e) {
            $messages = $e->errors()['status'] ?? [];
            // headline + 2 actions × 2 missing sign-offs
            $this->assertCount(5, $messages, 'one headline plus two blockers per action');
        }
    }

    #[Test]
    public function a_soft_deleted_action_does_not_block_closure(): void
    {
        $nc = $this->makeNc();

        $this->makeAction($nc, [
            'completed_date'            => now()->toDateString(),
            'verified_at'               => now(),
            'effectiveness_verified_at' => now(),
        ]);

        $mistake = $this->makeAction($nc, ['action_text' => 'Added by mistake']);
        $mistake->delete();

        $nc->update(['status' => NonConformity::STATUS_CLOSED]);

        $this->assertSame(NonConformity::STATUS_CLOSED, $nc->fresh()->status);
    }

    #[Test]
    public function the_gate_does_not_block_moving_to_a_non_closed_status(): void
    {
        $nc = $this->makeNc();
        $this->makeAction($nc);

        $nc->update(['status' => NonConformity::STATUS_ROOT_CAUSE]);

        $this->assertSame(NonConformity::STATUS_ROOT_CAUSE, $nc->fresh()->status);
    }

    #[Test]
    public function an_already_closed_record_can_be_edited_without_re_running_the_gate(): void
    {
        // A record that closed before its actions changed must still be
        // editable for unrelated fields; otherwise the guard would lock the row.
        $nc = $this->makeNc();
        $this->makeAction($nc, [
            'verified_at'               => now(),
            'effectiveness_verified_at' => now(),
        ]);
        $nc->update(['status' => NonConformity::STATUS_CLOSED]);

        $closed = $nc->fresh();
        $closed->update(['process_area' => 'Finance']);

        $this->assertSame('Finance', $closed->fresh()->process_area);
        $this->assertSame(NonConformity::STATUS_CLOSED, $closed->fresh()->status);
    }

    #[Test]
    public function the_can_close_accessor_agrees_with_the_gate(): void
    {
        $nc = $this->makeNc();
        $this->assertFalse($nc->can_close);

        $this->makeAction($nc, [
            'verified_at'               => now(),
            'effectiveness_verified_at' => now(),
        ]);

        $this->assertTrue($nc->fresh()->can_close);
        $this->assertSame([], $nc->fresh()->closure_blockers);
    }

    // -----------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------

    #[Test]
    public function a_non_conformity_has_many_corrective_actions(): void
    {
        $nc = $this->makeNc();
        $this->makeAction($nc, ['action_text' => 'First']);
        $this->makeAction($nc, ['action_text' => 'Second']);

        $this->assertCount(2, $nc->fresh()->correctiveActions);
    }

    #[Test]
    public function an_action_belongs_to_its_non_conformity(): void
    {
        $nc = $this->makeNc();
        $action = $this->makeAction($nc);

        $this->assertSame($nc->id, $action->fresh()->nonConformity->id);
    }

    #[Test]
    public function deleting_a_non_conformity_soft_deletes_it(): void
    {
        $nc = $this->makeNc();
        $nc->delete();

        $this->assertSoftDeleted('non_conformities', ['id' => $nc->id]);
    }

    // -----------------------------------------------------------------
    // Derived status on actions
    // -----------------------------------------------------------------

    #[Test]
    public function an_action_status_follows_its_sign_offs(): void
    {
        $nc = $this->makeNc();

        $pending = $this->makeAction($nc);
        $this->assertSame(CorrectiveAction::STATUS_PENDING, $pending->fresh()->status);

        $completed = $this->makeAction($nc, ['completed_date' => now()->toDateString()]);
        $this->assertSame(CorrectiveAction::STATUS_COMPLETED, $completed->fresh()->status);

        $verified = $this->makeAction($nc, [
            'completed_date' => now()->toDateString(),
            'verified_at'    => now(),
        ]);
        $this->assertSame(CorrectiveAction::STATUS_VERIFIED, $verified->fresh()->status);

        $effective = $this->makeAction($nc, [
            'completed_date'            => now()->toDateString(),
            'verified_at'               => now(),
            'effectiveness_verified_at' => now(),
        ]);
        $this->assertSame(CorrectiveAction::STATUS_EFFECTIVE, $effective->fresh()->status);
    }

    #[Test]
    public function effectiveness_requires_its_own_date_even_when_verified(): void
    {
        $nc = $this->makeNc();
        $action = $this->makeAction($nc, ['verified_at' => now()]);

        $this->assertTrue($action->fresh()->is_verified);
        $this->assertFalse($action->fresh()->is_effective, 'verified is not the same claim as effective');
    }

    // -----------------------------------------------------------------
    // Tenancy and presentation
    // -----------------------------------------------------------------

    #[Test]
    public function a_non_conformity_is_scoped_to_its_tenant(): void
    {
        $other = Agency::factory()->create();
        NonConformity::withoutGlobalScopes()->create([
            'agency_id'     => $other->id,
            'ref_no'        => 'NC-FOREIGN',
            'source'        => NonConformity::SOURCE_AUDIT,
            'detected_date' => now()->toDateString(),
            'description'   => 'Not mine',
            'status'        => NonConformity::STATUS_OPEN,
        ]);

        $mine = $this->makeNc(['ref_no' => 'NC-MINE']);

        $this->assertSame(1, NonConformity::query()->count());
        $this->assertSame('NC-MINE', NonConformity::query()->first()->ref_no);
    }

    #[Test]
    public function ref_no_is_unique_per_agency(): void
    {
        $this->makeNc(['ref_no' => 'NC-DUP']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->makeNc(['ref_no' => 'NC-DUP']);
    }

    #[Test]
    public function every_status_and_severity_has_a_label_and_badge(): void
    {
        foreach (NonConformity::STATUSES as $status) {
            $this->assertArrayHasKey($status, NonConformity::STATUS_LABELS);
            $this->assertArrayHasKey($status, NonConformity::STATUS_BADGES);
        }

        foreach (array_keys(NonConformity::SEVERITIES) as $severity) {
            $this->assertArrayHasKey($severity, NonConformity::SEVERITY_BADGES);
        }
    }

    #[Test]
    public function an_overdue_non_conformity_is_flagged(): void
    {
        $overdue = $this->makeNc(['due_date' => now()->subDays(5)->toDateString()]);
        $this->assertTrue($overdue->is_overdue);

        $stillLive = $this->makeNc([
            'due_date' => now()->subDays(5)->toDateString(),
            'status'   => NonConformity::STATUS_VERIFYING,
        ]);
        $this->assertTrue($stillLive->is_overdue, 'anything not closed is still live work');

        $insideWindow = $this->makeNc(['due_date' => now()->addDays(5)->toDateString()]);
        $this->assertFalse($insideWindow->is_overdue, 'a future due date is not overdue');
    }

    #[Test]
    public function creating_a_non_conformity_writes_an_audit_event(): void
    {
        $nc = $this->makeNc(['ref_no' => 'NC-AUDITED']);

        $this->assertDatabaseHas('audit_events', [
            'subject_type'  => NonConformity::class,
            'subject_id'    => $nc->id,
            'action'        => 'created',
            'subject_label' => 'NC-AUDITED',
        ]);
    }
}
