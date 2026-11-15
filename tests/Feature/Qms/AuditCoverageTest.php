<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\AuditEvent;
use App\Models\Payment;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-02 (card 6afa0d9f10479ec73cce2ba3) — audit coverage on the money modules.
 *
 * Proves the AuditsChanges trait writes exactly one audit event per material
 * change, with the right action, actor, tenant and a changed-fields-only diff.
 *
 * Deliberately asserts the *diff shape* rather than just "a row appeared":
 * an event with the whole model dumped into `after` is noise, not audit.
 */
class AuditCoverageTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->actor  = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function receivable(array $overrides = []): Receivable
    {
        return Receivable::create(array_merge([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->actor->id,
            'code'      => '000668',
            'date'      => now()->toDateString(),
            'status'    => Receivable::STATUS_PENDING,
            'amount'    => 3150.50,
            'account'   => 'Placement Fee',
            'type'      => 'Full Payment',
            'mode'      => 'GCash',
        ], $overrides));
    }

    #[Test]
    public function creating_a_receivable_writes_one_created_event_with_the_actor(): void
    {
        $this->actingAs($this->actor);

        $receivable = $this->receivable();

        $events = AuditEvent::withoutGlobalScopes()
            ->where('subject_type', Receivable::class)
            ->where('subject_id', $receivable->id)
            ->get();

        $this->assertCount(1, $events);

        $event = $events->first();
        $this->assertSame(AuditEvent::ACTION_CREATED, $event->action);
        $this->assertSame($this->agency->id, (int) $event->agency_id);
        $this->assertSame($this->actor->id, (int) $event->actor_user_id);
        $this->assertSame('000668', $event->subject_label);
        $this->assertNull($event->before);
        $this->assertSame(Receivable::STATUS_PENDING, $event->after['status']);
    }

    #[Test]
    public function updating_a_receivable_records_only_the_changed_fields(): void
    {
        $this->actingAs($this->actor);
        $receivable = $this->receivable();

        $receivable->update(['status' => Receivable::STATUS_RECEIVED]);

        $event = AuditEvent::withoutGlobalScopes()
            ->where('subject_type', Receivable::class)
            ->where('subject_id', $receivable->id)
            ->where('action', AuditEvent::ACTION_UPDATED)
            ->sole();

        $this->assertSame(Receivable::STATUS_PENDING, $event->before['status']);
        $this->assertSame(Receivable::STATUS_RECEIVED, $event->after['status']);

        // A whole-model dump would carry these; a real diff must not.
        $this->assertArrayNotHasKey('amount', $event->after);
        $this->assertArrayNotHasKey('account', $event->after);
        $this->assertArrayNotHasKey('updated_at', $event->after);
    }

    #[Test]
    public function an_update_that_changes_nothing_meaningful_writes_no_event(): void
    {
        $this->actingAs($this->actor);
        $receivable = $this->receivable();

        $before = AuditEvent::withoutGlobalScopes()->count();
        $receivable->touch(); // only updated_at moves
        $after = AuditEvent::withoutGlobalScopes()->count();

        $this->assertSame($before, $after, 'Touching a record must not create audit noise.');
    }

    #[Test]
    public function deleting_a_receivable_writes_a_deleted_event_carrying_the_last_state(): void
    {
        $this->actingAs($this->actor);
        $receivable = $this->receivable();
        $id = $receivable->id;

        $receivable->delete(); // soft delete — the row survives, the event records it

        $event = AuditEvent::withoutGlobalScopes()
            ->where('subject_type', Receivable::class)
            ->where('subject_id', $id)
            ->where('action', AuditEvent::ACTION_DELETED)
            ->sole();

        $this->assertSame('000668', $event->subject_label);
        $this->assertSame(Receivable::STATUS_PENDING, $event->before['status']);
    }

    #[Test]
    public function creating_a_payment_writes_an_event_attributed_to_its_agency(): void
    {
        $this->actingAs($this->actor);

        $payment = Payment::create([
            'agency_id'    => $this->agency->id,
            'amount'       => 2500.00,
            'category'     => 'employer_cost',
            'type'         => 'cash',
            'reference_no' => 'OR-9001',
            'status'       => 'confirmed',
        ]);

        $event = AuditEvent::withoutGlobalScopes()
            ->where('subject_type', Payment::class)
            ->where('subject_id', $payment->id)
            ->sole();

        $this->assertSame(AuditEvent::ACTION_CREATED, $event->action);
        $this->assertSame($this->agency->id, (int) $event->agency_id);
    }

    #[Test]
    public function events_are_written_without_an_authenticated_actor_too(): void
    {
        // No actingAs: a system/console write has no user, and must still audit.
        $receivable = $this->receivable();

        $event = AuditEvent::withoutGlobalScopes()
            ->where('subject_type', Receivable::class)
            ->where('subject_id', $receivable->id)
            ->sole();

        $this->assertNull($event->actor_user_id);
        $this->assertSame($this->agency->id, (int) $event->agency_id);
    }
}
