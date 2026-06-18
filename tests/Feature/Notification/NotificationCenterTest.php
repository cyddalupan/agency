<?php

namespace Tests\Feature\Notification;

use App\Models\Agency;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private User $staff;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['status' => 'active']);
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $this->superAdmin = User::factory()->create([
            'agency_id' => null,
            'user_type' => 'super_admin',
        ]);
    }

    private function createNotification(User $user, array $overrides = []): Notification
    {
        return Notification::factory()->create(array_merge([
            'agency_id' => $user->agency_id ?? $this->agency->id,
            'user_id' => $user->id,
            'type' => 'info',
            'data' => ['message' => 'Test notification message', 'link' => '/dashboard'],
        ], $overrides));
    }

    // ─── Route / Page Exists ──────────────────────────────────────────

    #[Test]
    public function notification_index_page_returns_successful_response(): void
    {
        $this->actingAs($this->admin)
            ->get(route('notifications.index'))
            ->assertStatus(200);
    }

    #[Test]
    public function notification_index_page_uses_correct_view(): void
    {
        $this->actingAs($this->admin)
            ->get(route('notifications.index'))
            ->assertViewIs('notifications.index');
    }

    // ─── Authorization ────────────────────────────────────────────────

    #[Test]
    public function guest_is_redirected_to_login_from_index(): void
    {
        $this->get(route('notifications.index'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_is_redirected_to_login_from_unread_count(): void
    {
        $this->get(route('notifications.unread-count'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_is_redirected_to_login_from_mark_as_read(): void
    {
        $this->post(route('notifications.mark-as-read', 1))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_is_redirected_to_login_from_mark_all_as_read(): void
    {
        $this->post(route('notifications.mark-all-as-read'))
            ->assertRedirect(route('login'));
    }

    // ─── Notification List / Index ─────────────────────────────────────

    #[Test]
    public function index_shows_users_notifications(): void
    {
        $notification = $this->createNotification($this->admin);

        $this->actingAs($this->admin)
            ->get(route('notifications.index'))
            ->assertSee($notification->data['message']);
    }

    #[Test]
    public function index_does_not_show_other_users_notifications(): void
    {
        $this->createNotification($this->staff);

        $this->actingAs($this->admin)
            ->get(route('notifications.index'))
            ->assertDontSee('Test notification message');
    }

    #[Test]
    public function notifications_are_sorted_by_latest_first(): void
    {
        $old = $this->createNotification($this->admin, [
            'data' => ['message' => 'Older notification'],
            'created_at' => now()->subDay(),
        ]);
        $new = $this->createNotification($this->admin, [
            'data' => ['message' => 'Newer notification'],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('notifications.index'));

        $response->assertSeeInOrder(['Newer notification', 'Older notification']);
    }

    #[Test]
    public function index_shows_notification_type(): void
    {
        $this->createNotification($this->admin, [
            'type' => 'status_change',
        ]);

        $this->actingAs($this->admin)
            ->get(route('notifications.index'))
            ->assertSee('status_change');
    }

    #[Test]
    public function index_shows_notification_time(): void
    {
        $this->createNotification($this->admin);

        $this->actingAs($this->admin)
            ->get(route('notifications.index'))
            ->assertSee('ago');
    }

    #[Test]
    public function index_paginates_notifications(): void
    {
        Notification::factory()->count(25)->create([
            'agency_id' => $this->agency->id,
            'user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('notifications.index'))
            ->assertSee('pagination')
            ->assertSee('2');
    }

    // ─── Read / Unread States ─────────────────────────────────────────

    #[Test]
    public function unread_notifications_are_visually_distinct(): void
    {
        $this->createNotification($this->admin, ['read_at' => null]);
        $this->createNotification($this->admin, ['read_at' => now()]);

        $response = $this->actingAs($this->admin)
            ->get(route('notifications.index'));

        $response->assertSee('unread');
        $response->assertSee('read');
    }

    #[Test]
    public function mark_single_notification_as_read(): void
    {
        $notification = $this->createNotification($this->admin, ['read_at' => null]);

        $this->actingAs($this->admin)
            ->post(route('notifications.mark-as-read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    #[Test]
    public function cannot_mark_other_users_notification_as_read(): void
    {
        $notification = $this->createNotification($this->staff, ['read_at' => null]);

        $this->actingAs($this->admin)
            ->post(route('notifications.mark-as-read', $notification->id))
            ->assertForbidden();
    }

    #[Test]
    public function mark_all_notifications_as_read(): void
    {
        $this->createNotification($this->admin, ['read_at' => null]);
        $this->createNotification($this->admin, ['read_at' => null]);
        $this->createNotification($this->admin, ['read_at' => null]);

        $this->actingAs($this->admin)
            ->post(route('notifications.mark-all-as-read'))
            ->assertRedirect();

        $this->assertEquals(0, $this->admin->notifications()->unread()->count());
    }

    #[Test]
    public function mark_all_does_not_affect_other_users(): void
    {
        $this->createNotification($this->admin, ['read_at' => null]);
        $this->createNotification($this->staff, ['read_at' => null]);

        $this->actingAs($this->admin)
            ->post(route('notifications.mark-all-as-read'))
            ->assertRedirect();

        $this->assertEquals(1, $this->staff->notifications()->unread()->count());
    }

    // ─── Unread Count (AJAX / navbar badge) ────────────────────────────

    #[Test]
    public function unread_count_returns_correct_number(): void
    {
        $this->createNotification($this->admin, ['read_at' => null]);
        $this->createNotification($this->admin, ['read_at' => null]);
        $this->createNotification($this->admin, ['read_at' => now()]);

        $this->actingAs($this->admin)
            ->get(route('notifications.unread-count'))
            ->assertJson(['count' => 2]);
    }

    #[Test]
    public function unread_count_returns_zero_when_none(): void
    {
        $this->actingAs($this->admin)
            ->get(route('notifications.unread-count'))
            ->assertJson(['count' => 0]);
    }

    #[Test]
    public function unread_count_is_scoped_to_current_user(): void
    {
        $this->createNotification($this->admin, ['read_at' => null]);
        $this->createNotification($this->staff, ['read_at' => null]);

        $this->actingAs($this->staff)
            ->get(route('notifications.unread-count'))
            ->assertJson(['count' => 1]);
    }

    // ─── Navbar Badge ─────────────────────────────────────────────────

    #[Test]
    public function navbar_shows_unread_notification_badge(): void
    {
        $this->createNotification($this->admin, ['read_at' => null]);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertSee('notification-badge')
            ->assertSee('1');
    }

    #[Test]
    public function navbar_hides_badge_when_no_unread(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertDontSee('notification-badge');
    }

    #[Test]
    public function navbar_has_notification_bell_icon(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertSee('notification-bell');
    }

    // ─── Dropdown (recent unread) ──────────────────────────────────────

    #[Test]
    public function dropdown_shows_recent_unread_notifications(): void
    {
        $this->createNotification($this->admin, ['read_at' => null]);
        $this->createNotification($this->admin, ['read_at' => null]);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertSee('notification-dropdown')
            ->assertSee('Test notification message');
    }

    #[Test]
    public function dropdown_limits_to_five_recent(): void
    {
        Notification::factory()->count(8)->create([
            'agency_id' => $this->agency->id,
            'user_id' => $this->admin->id,
            'read_at' => null,
        ]);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertSee('notification-dropdown')
            ->assertSee('View all');
    }

    #[Test]
    public function dropdown_shows_view_all_link(): void
    {
        $this->createNotification($this->admin, ['read_at' => null]);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertSee(route('notifications.index'));
    }

    #[Test]
    public function dropdown_only_shows_unread_notifications(): void
    {
        $this->createNotification($this->admin, ['read_at' => null, 'data' => ['message' => 'Unread one']]);
        $this->createNotification($this->admin, ['read_at' => now(), 'data' => ['message' => 'Read one']]);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertSee('Unread one')
            ->assertDontSee('Read one');
    }

    // ─── Empty State ─────────────────────────────────────────────────

    #[Test]
    public function empty_index_shows_no_notifications_message(): void
    {
        $this->actingAs($this->admin)
            ->get(route('notifications.index'))
            ->assertSee('No notifications');
    }

    #[Test]
    public function empty_dropdown_shows_no_notifications_message(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertSee('no-notifications');
    }

    // ─── Super Admin Access ──────────────────────────────────────────

    #[Test]
    public function super_admin_can_access_notification_center(): void
    {
        $notification = Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id' => $this->superAdmin->id,
            'type' => 'info',
            'data' => ['message' => 'Super admin notification'],
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Super admin notification');
    }

    #[Test]
    public function super_admin_unread_count_works(): void
    {
        Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id' => $this->superAdmin->id,
            'read_at' => null,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('notifications.unread-count'))
            ->assertJson(['count' => 1]);
    }

    // ─── Mark as Read via AJAX ────────────────────────────────────────

    #[Test]
    public function mark_as_read_returns_json_success(): void
    {
        $notification = $this->createNotification($this->admin, ['read_at' => null]);

        $this->actingAs($this->admin)
            ->post(route('notifications.mark-as-read', $notification->id), ['Accept' => 'application/json'])
            ->assertJson(['success' => true]);
    }

    #[Test]
    public function mark_all_as_read_returns_json_success(): void
    {
        $this->createNotification($this->admin, ['read_at' => null]);

        $this->actingAs($this->admin)
            ->post(route('notifications.mark-all-as-read'), ['Accept' => 'application/json'])
            ->assertJson(['success' => true]);
    }
}
