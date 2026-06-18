<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * RED PHASE: Tests for user activation/suspension and activity logging.
 *
 * None of these routes/models exist yet — every test will FAIL.
 * The GREEN phase will implement:
 *   - PUT /users/{user}/activate  (set status to active)
 *   - PUT /users/{user}/suspend   (set status to suspended)
 *   - PUT /users/{user}/deactivate (set status to inactive)
 *   - ActivityLog model + migration (subject, action, metadata, user_id)
 *   - Activity log display on user show page
 *   - Admin/super_admin authorization + agency scoping
 */
class UserActivationActivityTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    // ─── USER ACTIVATION / SUSPENSION ─────────────────────────────────

    #[Test]
    public function admin_can_activate_a_user(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'status'    => 'inactive',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('users.activate', $target));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'     => $target->id,
            'status' => 'active',
        ]);
    }

    #[Test]
    public function admin_can_suspend_a_user(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'status'    => 'active',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('users.suspend', $target));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'     => $target->id,
            'status' => 'suspended',
        ]);
    }

    #[Test]
    public function admin_can_deactivate_a_user(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'status'    => 'active',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('users.deactivate', $target));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'     => $target->id,
            'status' => 'inactive',
        ]);
    }

    #[Test]
    public function admin_cannot_suspend_themselves(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('users.suspend', $admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id'     => $admin->id,
            'status' => 'active',
        ]);
    }

    #[Test]
    public function staff_cannot_activate_other_users(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'status'    => 'inactive',
        ]);

        $response = $this->actingAs($staff)
            ->put(route('users.activate', $target));

        $response->assertForbidden();
    }

    #[Test]
    public function staff_cannot_suspend_other_users(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'status'    => 'active',
        ]);

        $response = $this->actingAs($staff)
            ->put(route('users.suspend', $target));

        $response->assertForbidden();
    }

    #[Test]
    public function admin_cannot_activate_user_from_other_agency(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $otherAgency = Agency::factory()->create();
        $otherUser = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'status'    => 'inactive',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('users.activate', $otherUser));

        $response->assertForbidden();
    }

    #[Test]
    public function admin_cannot_suspend_user_from_other_agency(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $otherAgency = Agency::factory()->create();
        $otherUser = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'status'    => 'active',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('users.suspend', $otherUser));

        $response->assertForbidden();
    }

    #[Test]
    public function super_admin_can_activate_across_agencies(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $otherAgency = Agency::factory()->create();
        $target = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'status'    => 'inactive',
        ]);

        $response = $this->actingAs($superAdmin)
            ->put(route('users.activate', $target));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'     => $target->id,
            'status' => 'active',
        ]);
    }

    #[Test]
    public function super_admin_can_suspend_across_agencies(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $otherAgency = Agency::factory()->create();
        $target = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'status'    => 'active',
        ]);

        $response = $this->actingAs($superAdmin)
            ->put(route('users.suspend', $target));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'     => $target->id,
            'status' => 'suspended',
        ]);
    }

    #[Test]
    public function guest_is_redirected_to_login_when_activating(): void
    {
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->put(route('users.activate', $target));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_is_redirected_to_login_when_suspending(): void
    {
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->put(route('users.suspend', $target));

        $response->assertRedirect(route('login'));
    }

    // ─── ACTIVITY LOGGING ─────────────────────────────────────────────

    #[Test]
    public function activity_logs_table_exists(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasTable('activity_logs'),
            'activity_logs table must exist'
        );
    }

    #[Test]
    public function activity_log_has_expected_columns(): void
    {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('activity_logs');
        $this->assertContains('id', $columns);
        $this->assertContains('user_id', $columns);
        $this->assertContains('subject_type', $columns);
        $this->assertContains('subject_id', $columns);
        $this->assertContains('action', $columns);
        $this->assertContains('description', $columns);
        $this->assertContains('metadata', $columns);
        $this->assertContains('agency_id', $columns);
        $this->assertContains('created_at', $columns);
    }

    #[Test]
    public function activity_log_can_be_created(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $log = ActivityLog::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $admin->id,
            'subject_type' => User::class,
            'subject_id'   => $admin->id,
            'action'       => 'login',
            'description'  => 'User logged in',
            'metadata'     => json_encode(['ip' => '127.0.0.1']),
        ]);

        $this->assertModelExists($log);
        $this->assertEquals($admin->id, $log->user_id);
        $this->assertEquals('login', $log->action);
    }

    #[Test]
    public function activity_log_belongs_to_user(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $log = ActivityLog::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $admin->id,
        ]);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($admin->id, $log->user->id);
    }

    #[Test]
    public function activating_a_user_creates_activity_log(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'status'    => 'inactive',
        ]);

        $this->actingAs($admin)
            ->put(route('users.activate', $target));

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $admin->id,
            'subject_type' => User::class,
            'subject_id'   => $target->id,
            'action'       => 'activated',
        ]);
    }

    #[Test]
    public function suspending_a_user_creates_activity_log(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'status'    => 'active',
        ]);

        $this->actingAs($admin)
            ->put(route('users.suspend', $target));

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $admin->id,
            'subject_type' => User::class,
            'subject_id'   => $target->id,
            'action'       => 'suspended',
        ]);
    }

    #[Test]
    public function deactivating_a_user_creates_activity_log(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'status'    => 'active',
        ]);

        $this->actingAs($admin)
            ->put(route('users.deactivate', $target));

        $this->assertDatabaseHas('activity_logs', [
            'agency_id'    => $this->agency->id,
            'user_id'      => $admin->id,
            'subject_type' => User::class,
            'subject_id'   => $target->id,
            'action'       => 'deactivated',
        ]);
    }

    // ─── ACTIVITY LOG DISPLAY ─────────────────────────────────────────

    #[Test]
    public function user_show_page_displays_activity_log(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        // Create some activity logs for the target user
        ActivityLog::factory()->count(3)->create([
            'agency_id'    => $this->agency->id,
            'subject_type' => User::class,
            'subject_id'   => $target->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.show', $target));

        $response->assertOk();
        // The show page should have an activity log section
        $response->assertViewHas('activities');
    }

    #[Test]
    public function user_show_page_shows_no_activities_message(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.show', $target));

        $response->assertOk();
        // Should show a message when no activities exist
        $response->assertSee('No');
    }

    // ─── ACTIVATE/SUSPEND BUTTONS ON USERS INDEX ──────────────────────

    #[Test]
    public function users_index_shows_activate_button_for_inactive_users(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $inactiveUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'status'    => 'inactive',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.index'));

        $response->assertOk();
        // The index should show an activate button/link for inactive users
        $response->assertSee(route('users.activate', $inactiveUser));
    }

    #[Test]
    public function users_index_shows_no_activate_button_for_self(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.index'));

        $response->assertOk();
        // Should NOT show activate/suspend button for the logged-in user themselves
        $response->assertDontSee('Activate');
    }
}
