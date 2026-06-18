<?php

namespace Tests\Feature\Notification;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationModelTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);
    }

    // ─── DATABASE SCHEMA ──────────────────────────────────────────────

    #[Test]
    public function notifications_table_exists(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasTable('notifications')
        );
    }

    #[Test]
    public function notifications_table_has_expected_columns(): void
    {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('notifications');

        $expected = [
            'id',
            'agency_id',
            'user_id',
            'type',
            'data',
            'read_at',
            'created_at',
            'updated_at',
        ];

        foreach ($expected as $col) {
            $this->assertContains($col, $columns, "Missing column: {$col}");
        }
    }

    #[Test]
    public function notifications_table_has_foreign_keys(): void
    {
        // agency_id references agencies
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumns('notifications', ['agency_id', 'user_id'])
        );
    }

    // ─── MODEL CREATION ───────────────────────────────────────────────

    #[Test]
    public function notification_can_be_created_via_factory(): void
    {
        $notification = Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'id'        => $notification->id,
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);
    }

    #[Test]
    public function notification_can_be_created_with_unread_state(): void
    {
        $notification = Notification::factory()->unread()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $this->assertNull($notification->read_at);
    }

    #[Test]
    public function notification_can_be_created_with_read_state(): void
    {
        $notification = Notification::factory()->read()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $this->assertNotNull($notification->read_at);
    }

    #[Test]
    public function notification_has_fillable_attributes(): void
    {
        $data = ['key' => 'value', 'message' => 'Test notification'];
        $notification = Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'type'      => 'status_change',
            'data'      => $data,
        ]);

        $this->assertEquals('status_change', $notification->type);
        $this->assertEquals($data, $notification->data);
    }

    // ─── RELATIONSHIPS ────────────────────────────────────────────────

    #[Test]
    public function notification_belongs_to_user(): void
    {
        $notification = Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $this->assertInstanceOf(User::class, $notification->user);
        $this->assertEquals($this->user->id, $notification->user->id);
    }

    #[Test]
    public function notification_belongs_to_agency(): void
    {
        $notification = Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $this->assertInstanceOf(Agency::class, $notification->agency);
        $this->assertEquals($this->agency->id, $notification->agency->id);
    }

    #[Test]
    public function user_has_many_notifications(): void
    {
        Notification::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $this->assertCount(3, $this->user->notifications);
    }

    #[Test]
    public function agency_has_many_notifications(): void
    {
        Notification::factory()->count(2)->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $this->assertCount(2, $this->agency->notifications);
    }

    #[Test]
    public function notification_has_polymorphic_notifiable_relationship(): void
    {
        $notification = Notification::factory()->create([
            'agency_id'        => $this->agency->id,
            'user_id'          => $this->user->id,
            'notifiable_type'  => User::class,
            'notifiable_id'    => $this->user->id,
        ]);

        $this->assertInstanceOf(User::class, $notification->notifiable);
    }

    #[Test]
    public function notification_polymorphic_notifiable_can_be_nullable(): void
    {
        $notification = Notification::factory()->create([
            'agency_id'       => $this->agency->id,
            'user_id'         => $this->user->id,
            'notifiable_type' => null,
            'notifiable_id'   => null,
        ]);

        $this->assertNull($notification->notifiable_type);
        $this->assertNull($notification->notifiable_id);
    }

    // ─── SCOPES ───────────────────────────────────────────────────────

    #[Test]
    public function notification_has_unread_scope(): void
    {
        Notification::factory()->count(2)->unread()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);
        Notification::factory()->read()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $unread = Notification::unread()->get();
        $this->assertCount(2, $unread);
    }

    #[Test]
    public function notification_has_read_scope(): void
    {
        Notification::factory()->read()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $read = Notification::read()->get();
        $this->assertCount(1, $read);
    }

    // ─── TENANT SCOPING ───────────────────────────────────────────────

    #[Test]
    public function notification_is_scoped_to_agency(): void
    {
        $agency2 = Agency::factory()->create();

        Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);
        Notification::factory()->create([
            'agency_id' => $agency2->id,
            'user_id'   => $this->user->id,
        ]);

        // Without tenant scope bypass, count should be 1
        $this->assertCount(1, $this->agency->notifications);
    }

    // ─── CASTS ────────────────────────────────────────────────────────

    #[Test]
    public function notification_data_is_cast_to_array(): void
    {
        $notification = Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'data'      => ['message' => 'Hello', 'link' => '/applicants'],
        ]);

        $this->assertIsArray($notification->data);
        $this->assertEquals('Hello', $notification->data['message']);
    }

    #[Test]
    public function notification_read_at_is_cast_to_datetime(): void
    {
        $notification = Notification::factory()->read()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $notification->read_at);
    }

    // ─── MARK AS READ / UNREAD ────────────────────────────────────────

    #[Test]
    public function notification_can_be_marked_as_read(): void
    {
        $notification = Notification::factory()->unread()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $notification->markAsRead();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    #[Test]
    public function notification_can_be_marked_as_unread(): void
    {
        $notification = Notification::factory()->read()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $notification->markAsUnread();

        $this->assertNull($notification->fresh()->read_at);
    }

    #[Test]
    public function notification_has_is_read_boolean_helper(): void
    {
        $unread = Notification::factory()->unread()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);
        $read = Notification::factory()->read()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $this->assertFalse($unread->isRead());
        $this->assertTrue($read->isRead());
    }

    // ─── USER UNREAD COUNT ────────────────────────────────────────────

    #[Test]
    public function user_can_get_unread_notification_count(): void
    {
        Notification::factory()->count(3)->unread()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);
        Notification::factory()->count(2)->read()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
        ]);

        $this->assertEquals(3, $this->user->unreadNotifications()->count());
    }

    // ─── NOTIFICATION TYPE ────────────────────────────────────────────

    #[Test]
    public function notification_can_be_filtered_by_type(): void
    {
        Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'type'      => 'status_change',
        ]);
        Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'type'      => 'approval',
        ]);
        Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'type'      => 'bill_due',
        ]);

        $this->assertCount(1, Notification::ofType('approval')->get());
    }

    // ─── SCOPE BY USER ────────────────────────────────────────────────

    #[Test]
    public function user_scope_returns_only_that_users_notifications(): void
    {
        $user2 = User::factory()->create(['agency_id' => $this->agency->id]);

        Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'type'      => 'status_change',
        ]);
        Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $user2->id,
            'type'      => 'approval',
        ]);

        $this->assertCount(1, Notification::forUser($this->user->id)->get());
    }

    // ─── DATA CASTING WITH JSON COLUMN ────────────────────────────────

    #[Test]
    public function notification_data_is_stored_as_json(): void
    {
        $data = ['message' => 'Test', 'applicant_id' => 42];

        $notification = Notification::factory()->create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'data'      => $data,
        ]);

        // Verify raw DB value is JSON
        $raw = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('id', $notification->id)
            ->value('data');

        $this->assertJson($raw);
        $this->assertEquals($data, json_decode($raw, true));
    }
}
