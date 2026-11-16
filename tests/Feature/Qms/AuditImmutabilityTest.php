<?php

namespace Tests\Feature\Qms;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\AuditEvent;
use App\Models\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

/**
 * QMS-05 (card 6afa0da26845f0db92f0f9c7): audit events cannot be edited or
 * deleted through ordinary application functions (spec §26).
 */
class AuditImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agency = Agency::factory()->withQms()->create();
    }

    private function makeEvent(): AuditEvent
    {
        return AuditEvent::withoutGlobalScopes()->create([
            'agency_id'     => $this->agency->id,
            'actor_user_id' => null,
            'action'        => AuditEvent::ACTION_CREATED,
            'subject_type'  => 'App\\Models\\Receivable',
            'subject_id'    => 1,
            'subject_label' => 'QMS05',
            'before'        => [],
            'after'         => ['status' => 'pending'],
        ]);
    }

    #[Test]
    public function an_audit_event_can_be_created_and_read_back(): void
    {
        $event = $this->makeEvent();

        $this->assertNotNull($event->id);
        $this->assertSame('pending', $event->fresh()->after['status']);
    }

    #[Test]
    public function updating_an_existing_audit_event_throws(): void
    {
        $event = $this->makeEvent();

        $this->expectException(RuntimeException::class);

        $event->action = AuditEvent::ACTION_ARCHIVED;
        $event->save();
    }

    #[Test]
    public function the_database_row_is_unchanged_after_a_blocked_update(): void
    {
        $event = $this->makeEvent();

        try {
            $event->action = AuditEvent::ACTION_ARCHIVED;
            $event->save();
        } catch (RuntimeException $e) {
            // expected
        }

        $this->assertSame(
            AuditEvent::ACTION_CREATED,
            AuditEvent::withoutGlobalScopes()->find($event->id)->action
        );
    }

    #[Test]
    public function deleting_an_existing_audit_event_throws(): void
    {
        $event = $this->makeEvent();

        $this->expectException(RuntimeException::class);

        $event->delete();
    }

    #[Test]
    public function the_database_row_survives_a_blocked_delete(): void
    {
        $event = $this->makeEvent();

        try {
            $event->delete();
        } catch (RuntimeException $e) {
            // expected
        }

        $this->assertDatabaseHas('audit_events', ['id' => $event->id]);
    }

    #[Test]
    public function force_deleting_an_audit_event_also_throws(): void
    {
        $event = $this->makeEvent();

        $this->expectException(RuntimeException::class);

        $event->forceDelete();
    }

    #[Test]
    public function mass_delete_through_the_query_builder_also_throws(): void
    {
        $event = $this->makeEvent();

        $this->expectException(RuntimeException::class);

        AuditEvent::withoutGlobalScopes()->where('id', $event->id)->delete();
    }

    #[Test]
    public function updating_an_existing_activity_log_throws(): void
    {
        $log = ActivityLog::create([
            'agency_id' => $this->agency->id,
            'action'    => 'login',
            'description' => 'test',
        ]);

        $this->expectException(RuntimeException::class);

        $log->description = 'tampered';
        $log->save();
    }

    #[Test]
    public function deleting_an_existing_activity_log_throws(): void
    {
        $log = ActivityLog::create([
            'agency_id' => $this->agency->id,
            'action'    => 'login',
            'description' => 'test',
        ]);

        $this->expectException(RuntimeException::class);

        $log->delete();
    }

    /**
     * The QMS-02 trait writes an audit event when an *audited* model is hard
     * deleted. That write is a create, so it must keep working under the guard —
     * otherwise a force-delete elsewhere would start throwing.
     *
     * @test
     */
    public function an_audited_model_can_still_be_hard_deleted(): void
    {
        $model = new class extends Model {
            use AuditsChanges;

            protected $table = 'starting_balances';
            protected $guarded = [];
            public $timestamps = true;
        };

        $row = $model->create(['account' => 'QMS05', 'amount' => 10, 'agency_id' => $this->agency->id]);
        $row->forceDelete();

        // The delete was recorded, not blocked.
        $this->assertGreaterThan(0, AuditEvent::withoutGlobalScopes()->count());
    }
}
