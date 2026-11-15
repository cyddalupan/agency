<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-03 (card 6afa0da0a3bf2c5d4e09e0cf) — the read-only Audit Trail screen.
 *
 * Locks the three promises on the card: an admin sees the trail, every other
 * role gets 403, and each filter actually narrows the result set.
 */
class AuditTrailScreenTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin  = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function event(array $overrides = []): AuditEvent
    {
        return AuditEvent::withoutGlobalScopes()->create(array_merge([
            'agency_id'     => $this->agency->id,
            'actor_user_id' => $this->admin->id,
            'action'        => AuditEvent::ACTION_UPDATED,
            'subject_type'  => 'App\\Models\\Receivable',
            'subject_id'    => 1,
            'subject_label' => '000668',
            'before'        => ['status' => 'pending'],
            'after'         => ['status' => 'received'],
        ], $overrides));
    }

    #[Test]
    public function an_admin_can_open_the_audit_trail(): void
    {
        $this->event();

        $this->actingAs($this->admin)
            ->get(route('audit_trail.index'))
            ->assertOk()
            ->assertSee('Audit Trail')
            ->assertSee('000668');
    }

    #[Test]
    public function a_super_admin_can_open_the_audit_trail(): void
    {
        $superAdmin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'super_admin',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('audit_trail.index'))
            ->assertOk();
    }

    #[Test]
    public function every_other_role_gets_a_403_not_a_redirect(): void
    {
        foreach (['accounting', 'recruitment', 'branch', 'welfare', 'agent', 'employer'] as $role) {
            $user = User::factory()->create([
                'agency_id' => $this->agency->id,
                'user_type' => $role,
            ]);

            $this->actingAs($user)
                ->get(route('audit_trail.index'))
                ->assertForbidden(); // asserts 403 specifically
        }
    }

    #[Test]
    public function the_action_filter_narrows_the_result_set(): void
    {
        $this->event(['action' => AuditEvent::ACTION_CREATED, 'subject_label' => 'CREATED-ONE']);
        $this->event(['action' => AuditEvent::ACTION_UPDATED, 'subject_label' => 'UPDATED-ONE']);

        // Unfiltered shows both.
        $this->actingAs($this->admin)
            ->get(route('audit_trail.index'))
            ->assertOk()
            ->assertSee('CREATED-ONE')
            ->assertSee('UPDATED-ONE');

        // Filtered to created shows only one.
        $this->actingAs($this->admin)
            ->get(route('audit_trail.index', ['action' => AuditEvent::ACTION_CREATED]))
            ->assertOk()
            ->assertSee('CREATED-ONE')
            ->assertDontSee('UPDATED-ONE');
    }

    #[Test]
    public function the_record_type_filter_narrows_the_result_set(): void
    {
        $this->event(['subject_type' => 'App\\Models\\Receivable', 'subject_label' => 'REC-1']);
        $this->event(['subject_type' => 'App\\Models\\Payment', 'subject_label' => 'PAY-1']);

        $this->actingAs($this->admin)
            ->get(route('audit_trail.index', ['subject_type' => 'App\\Models\\Payment']))
            ->assertOk()
            ->assertSee('PAY-1')
            ->assertDontSee('REC-1');
    }

    #[Test]
    public function the_actor_filter_narrows_the_result_set(): void
    {
        $other = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $this->event(['actor_user_id' => $this->admin->id, 'subject_label' => 'MINE']);
        $this->event(['actor_user_id' => $other->id, 'subject_label' => 'THEIRS']);

        $this->actingAs($this->admin)
            ->get(route('audit_trail.index', ['actor' => $other->id]))
            ->assertOk()
            ->assertSee('THEIRS')
            ->assertDontSee('MINE');
    }

    #[Test]
    public function the_free_text_search_matches_the_subject_label(): void
    {
        $this->event(['subject_label' => '000668']);
        $this->event(['subject_label' => '000999']);

        $this->actingAs($this->admin)
            ->get(route('audit_trail.index', ['q' => '000668']))
            ->assertOk()
            ->assertSee('000668')
            ->assertDontSee('000999');
    }

    #[Test]
    public function the_date_range_filter_excludes_events_outside_it(): void
    {
        // created_at is stamped by Eloquent on insert, so the past date is
        // forced afterwards rather than passed to create().
        $old = $this->event(['subject_label' => 'OLD-EVENT']);
        AuditEvent::withoutGlobalScopes()->where('id', $old->id)
            ->update(['created_at' => now()->subDays(30)]);

        $this->event(['subject_label' => 'NEW-EVENT']);

        $this->actingAs($this->admin)
            ->get(route('audit_trail.index', ['from' => now()->subDay()->toDateString()]))
            ->assertOk()
            ->assertSee('NEW-EVENT')
            ->assertDontSee('OLD-EVENT');
    }

    #[Test]
    public function the_screen_renders_the_diff_as_fields_rather_than_raw_json(): void
    {
        $this->event();

        $response = $this->actingAs($this->admin)->get(route('audit_trail.index'));

        $response->assertOk();

        // Read the "Fields changed" cell itself. Asserting assertSee('status') on
        // the whole page passed even when the column rendered empty, because the
        // word occurs elsewhere in the markup — so assert the cell's own text.
        $html = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/Fields changed.*?<span class="font-mono">([^<]*)<\/span>/s',
            $html
        );
        preg_match('/Fields changed.*?<span class="font-mono">([^<]*)<\/span>/s', $html, $m);
        $this->assertSame('status', trim($m[1]), 'The changed-fields column must name the field that moved.');

        // The expanded detail table carries the before and after values.
        $response->assertSee('pending')->assertSee('received');
    }

    #[Test]
    public function an_empty_trail_says_so_instead_of_rendering_a_bare_table(): void
    {
        $this->actingAs($this->admin)
            ->get(route('audit_trail.index'))
            ->assertOk()
            ->assertSee('No audit events match these filters.');
    }
}
