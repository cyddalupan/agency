<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-01 (card 6afa0d9e19ea247279f0a1d8) — the audit spine.
 *
 * Locks the two things QMS-02..QMS-05 build on:
 *   1. an event round-trips through the model (json casts, relations);
 *   2. the table is append-only by shape — there is no `updated_at` column.
 *
 * The hard guard that *throws* on update/delete lands in QMS-05; this test
 * covers only what QMS-01 introduces.
 */
class AuditEventTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->withQms()->create();
        $this->actor  = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function an_event_round_trips_with_its_json_payload_and_relations(): void
    {
        $event = AuditEvent::create([
            'agency_id'     => $this->agency->id,
            'actor_user_id' => $this->actor->id,
            'action'        => AuditEvent::ACTION_UPDATED,
            'subject_type'  => 'App\\Models\\Receivable',
            'subject_id'    => 1093,
            'subject_label' => '000668',
            'before'        => ['status' => 'pending'],
            'after'         => ['status' => 'received'],
            'ip_address'    => '203.0.113.7',
            'user_agent'    => 'PHPUnit',
        ]);

        $fresh = AuditEvent::withoutGlobalScopes()->findOrFail($event->id);

        $this->assertSame(AuditEvent::ACTION_UPDATED, $fresh->action);
        $this->assertSame('000668', $fresh->subject_label);
        $this->assertSame(['status' => 'pending'], $fresh->before);
        $this->assertSame(['status' => 'received'], $fresh->after);
        $this->assertSame($this->actor->id, $fresh->actor->id);
        $this->assertSame('Updated', $fresh->action_label);
    }

    #[Test]
    public function the_table_has_no_updated_at_column_so_events_cannot_be_amended(): void
    {
        $columns = Schema::getColumnListing('audit_events');

        $this->assertContains('created_at', $columns);
        $this->assertNotContains('updated_at', $columns);
    }

    #[Test]
    public function every_action_in_the_constant_list_is_storable(): void
    {
        foreach (AuditEvent::ACTIONS as $action) {
            $event = AuditEvent::create([
                'agency_id'    => $this->agency->id,
                'action'       => $action,
                'subject_type' => 'App\\Models\\Receivable',
                'subject_id'   => 1,
            ]);

            $this->assertSame($action, $event->withoutGlobalScopes()->find($event->id)->action);
        }

        $this->assertSame(count(AuditEvent::ACTIONS), AuditEvent::withoutGlobalScopes()->count());
    }

    #[Test]
    public function the_tenant_scope_is_column_qualified_against_the_audit_events_table(): void
    {
        // TenantScope adds a where on the model's table. If the scope were
        // missing or unqualified, joins against users/agencies would be
        // ambiguous. Assert the qualification explicitly rather than trusting
        // request context to prove it.
        app()->bind('tenant_agency', fn () => $this->agency);

        $sql = AuditEvent::query()->toBase()->toSql();

        $this->assertMatchesRegularExpression('/"audit_events"\."agency_id"/', $sql);
    }
}
