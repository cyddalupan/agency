<?php

namespace Tests\Feature\Qms;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6afdc2b9 — Sensitive Actions screen.
 *
 * SensitiveActionLogger writes `activity_logs`; the Audit Trail screen reads
 * `audit_events`. Before this screen existed, 3357 rows of logins, failed
 * logins, role changes, exposures and admin password resets were unreachable
 * from the one page an auditor opens.
 *
 * The two spines stay separate: activity_logs has `description` + `metadata`,
 * not a before/after diff, so it gets its own screen and its own vocabulary.
 */
class AuditTrailSensitiveActionsTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->withQms()->create();
        $this->admin  = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function log(array $overrides = []): ActivityLog
    {
        return ActivityLog::create(array_merge([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'subject_type' => User::class,
            'subject_id'   => $this->admin->id,
            'action'       => 'login',
            'description'  => 'admin@test.com logged in.',
            'metadata'     => ['ip' => '203.0.113.9', 'user_agent' => 'phpunit'],
        ], $overrides));
    }

    #[Test]
    public function an_admin_can_open_the_sensitive_actions_screen(): void
    {
        $this->log();

        $this->actingAs($this->admin)
            ->get(route('audit_trail.sensitive'))
            ->assertOk()
            ->assertSee('Sensitive Actions')
            ->assertSee('admin@test.com logged in.');
    }

    #[Test]
    public function the_screen_renders_every_sensitive_action_kind(): void
    {
        $this->log(['action' => 'login', 'description' => 'ALPHA logged in.']);
        $this->log(['action' => 'role_changed', 'description' => 'BRAVO changed role.', 'metadata' => ['old_role' => 'staff', 'new_role' => 'manager']]);
        $this->log(['action' => 'user_password_reset_by_admin', 'description' => 'CHARLIE reset the password.', 'metadata' => ['target_email' => 'x@test.com', 'reset_by' => 1]]);

        $response = $this->actingAs($this->admin)->get(route('audit_trail.sensitive'));

        $response->assertOk();
        $response->assertSee('ALPHA logged in.');
        $response->assertSee('BRAVO changed role.');
        $response->assertSee('CHARLIE reset the password.');
        // Labels are humanised slugs, not the fixed audit_events vocabulary.
        $response->assertSee('Role changed');
        $response->assertSee('User password reset by admin');
    }

    #[Test]
    public function roles_outside_the_allowlist_get_403(): void
    {
        $this->log();

        foreach (['staff', 'billing', 'employer', 'recruiter', 'interviewer'] as $role) {
            $user = User::factory()->create([
                'agency_id' => $this->agency->id,
                'user_type' => $role,
            ]);

            $this->actingAs($user)
                ->get(route('audit_trail.sensitive'))
                ->assertForbidden();
        }
    }

    #[Test]
    public function the_action_filter_narrows_the_result_set(): void
    {
        $this->log(['action' => 'login', 'description' => 'login row']);
        $this->log(['action' => 'failed_login', 'description' => 'failed row']);

        $response = $this->actingAs($this->admin)
            ->get(route('audit_trail.sensitive', ['action' => 'failed_login']));

        $response->assertOk();
        $response->assertSee('failed row');
        $response->assertDontSee('login row');
    }

    #[Test]
    public function the_description_search_narrows_the_result_set(): void
    {
        $this->log(['action' => 'login', 'description' => 'unique-needle-here']);
        $this->log(['action' => 'login', 'description' => 'other row']);

        $response = $this->actingAs($this->admin)
            ->get(route('audit_trail.sensitive', ['q' => 'unique-needle-here']));

        $response->assertOk();
        $response->assertSee('unique-needle-here');
        $response->assertDontSee('other row');
    }

    #[Test]
    public function another_agencys_sensitive_actions_are_never_shown(): void
    {
        $this->log(['action' => 'login', 'description' => 'MINE-IN-THIS-AGENCY']);

        $otherAgency = Agency::factory()->withQms()->create();
        $foreignUser = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);
        ActivityLog::create([
            'agency_id'   => $otherAgency->id,
            'user_id'     => $foreignUser->id,
            'action'      => 'suspicious_activity',
            'description' => 'THEIR-SECRET-ACTIVITY',
            'metadata'    => [],
        ]);

        $response = $this->actingAs($this->admin)->get(route('audit_trail.sensitive'));

        $response->assertOk();
        $response->assertSee('MINE-IN-THIS-AGENCY');
        $response->assertDontSee('THEIR-SECRET-ACTIVITY');
    }

    #[Test]
    public function the_record_changes_screen_still_shows_only_audit_events(): void
    {
        // Guard against a future accidental union of the two spines: an
        // activity_logs row must not leak onto the audit_events screen.
        $this->log(['action' => 'login', 'description' => 'ACTIVITY-LOG-ONLY-ROW']);

        $response = $this->actingAs($this->admin)->get(route('audit_trail.index'));

        $response->assertOk();
        $response->assertDontSee('ACTIVITY-LOG-ONLY-ROW');
    }

    #[Test]
    public function the_two_screens_link_to_each_other(): void
    {
        $this->log();

        $this->actingAs($this->admin)
            ->get(route('audit_trail.index'))
            ->assertOk()
            ->assertSee('Sensitive actions');

        $this->actingAs($this->admin)
            ->get(route('audit_trail.sensitive'))
            ->assertOk()
            ->assertSee('Record changes');
    }
}
