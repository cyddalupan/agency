<?php

namespace Tests\Feature\Notification;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Bill;
use App\Models\Notification;
use App\Models\User;
use App\Events\ApplicantStatusChanged;
use App\Events\AgencyApproved;
use App\Events\AgencyRejected;
use App\Events\BillCreated;
use App\Events\PaymentReceived;
use App\Events\DocumentApproved;
use App\Events\DocumentRejected;
use App\Listeners\SendStatusChangeNotification;
use App\Listeners\SendAgencyApprovalNotification;
use App\Listeners\SendBillCreatedNotification;
use App\Listeners\SendPaymentReceivedNotification;
use App\Listeners\SendDocumentApprovalNotification;
use App\Listeners\SendDocumentRejectionNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationTriggerTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $adminUser;
    private User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['status' => 'active']);
        $this->adminUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->staffUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
    }

    // ─── EVENT CLASSES EXIST ──────────────────────────────────────────

    #[Test]
    public function applicant_status_changed_event_class_exists(): void
    {
        $this->assertTrue(class_exists(ApplicantStatusChanged::class));
    }

    #[Test]
    public function agency_approved_event_class_exists(): void
    {
        $this->assertTrue(class_exists(AgencyApproved::class));
    }

    #[Test]
    public function agency_rejected_event_class_exists(): void
    {
        $this->assertTrue(class_exists(AgencyRejected::class));
    }

    #[Test]
    public function bill_created_event_class_exists(): void
    {
        $this->assertTrue(class_exists(BillCreated::class));
    }

    #[Test]
    public function payment_received_event_class_exists(): void
    {
        $this->assertTrue(class_exists(PaymentReceived::class));
    }

    #[Test]
    public function document_approved_event_class_exists(): void
    {
        $this->assertTrue(class_exists(DocumentApproved::class));
    }

    #[Test]
    public function document_rejected_event_class_exists(): void
    {
        $this->assertTrue(class_exists(DocumentRejected::class));
    }

    // ─── LISTENER CLASSES EXIST ───────────────────────────────────────

    #[Test]
    public function send_status_change_notification_listener_exists(): void
    {
        $this->assertTrue(class_exists(SendStatusChangeNotification::class));
    }

    #[Test]
    public function send_agency_approval_notification_listener_exists(): void
    {
        $this->assertTrue(class_exists(SendAgencyApprovalNotification::class));
    }

    #[Test]
    public function send_bill_created_notification_listener_exists(): void
    {
        $this->assertTrue(class_exists(SendBillCreatedNotification::class));
    }

    #[Test]
    public function send_payment_received_notification_listener_exists(): void
    {
        $this->assertTrue(class_exists(SendPaymentReceivedNotification::class));
    }

    #[Test]
    public function send_document_approval_notification_listener_exists(): void
    {
        $this->assertTrue(class_exists(SendDocumentApprovalNotification::class));
    }

    #[Test]
    public function send_document_rejection_notification_listener_exists(): void
    {
        $this->assertTrue(class_exists(SendDocumentRejectionNotification::class));
    }

    // ─── EVENT-LISTENER BINDING ───────────────────────────────────────

    #[Test]
    public function applicant_status_changed_event_is_listened_to(): void
    {
        Event::fake();

        // Verify the event has a listener registered in EventServiceProvider
        $listeners = Event::getListeners(ApplicantStatusChanged::class);
        $this->assertNotEmpty($listeners, 'ApplicantStatusChanged has no registered listeners');
    }

    #[Test]
    public function agency_approved_event_is_listened_to(): void
    {
        Event::fake();

        $listeners = Event::getListeners(AgencyApproved::class);
        $this->assertNotEmpty($listeners, 'AgencyApproved has no registered listeners');
    }

    #[Test]
    public function agency_rejected_event_is_listened_to(): void
    {
        Event::fake();

        $listeners = Event::getListeners(AgencyRejected::class);
        $this->assertNotEmpty($listeners, 'AgencyRejected has no registered listeners');
    }

    #[Test]
    public function bill_created_event_is_listened_to(): void
    {
        Event::fake();

        $listeners = Event::getListeners(BillCreated::class);
        $this->assertNotEmpty($listeners, 'BillCreated has no registered listeners');
    }

    #[Test]
    public function payment_received_event_is_listened_to(): void
    {
        Event::fake();

        $listeners = Event::getListeners(PaymentReceived::class);
        $this->assertNotEmpty($listeners, 'PaymentReceived has no registered listeners');
    }

    #[Test]
    public function document_approved_event_is_listened_to(): void
    {
        Event::fake();

        $listeners = Event::getListeners(DocumentApproved::class);
        $this->assertNotEmpty($listeners, 'DocumentApproved has no registered listeners');
    }

    #[Test]
    public function document_rejected_event_is_listened_to(): void
    {
        Event::fake();

        $listeners = Event::getListeners(DocumentRejected::class);
        $this->assertNotEmpty($listeners, 'DocumentRejected has no registered listeners');
    }

    // ─── STATUS CHANGE → NOTIFICATION ─────────────────────────────────

    #[Test]
    public function dispatching_applicant_status_changed_creates_notification(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'status_code' => 0,
        ]);

        event(new ApplicantStatusChanged($applicant, 0, 6, $this->staffUser));

        $this->assertDatabaseHas('notifications', [
            'agency_id' => $this->agency->id,
            'type'      => 'status_change',
        ]);
    }

    #[Test]
    public function status_change_notification_targets_assigned_user(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'status_code' => 0,
        ]);

        event(new ApplicantStatusChanged($applicant, 0, 6, $this->staffUser));

        $notification = Notification::where('type', 'status_change')->first();

        $this->assertNotNull($notification);
        $this->assertEquals($this->staffUser->id, $notification->user_id);
    }

    #[Test]
    public function status_change_notification_contains_applicant_info(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
        ]);

        event(new ApplicantStatusChanged($applicant, 0, 6, $this->staffUser));

        $notification = Notification::where('type', 'status_change')->first();

        $this->assertNotNull($notification);
        $this->assertArrayHasKey('applicant_name', $notification->data);
        $this->assertArrayHasKey('from_status', $notification->data);
        $this->assertArrayHasKey('to_status', $notification->data);
        $this->assertStringContainsString('Juan', $notification->data['applicant_name']);
    }

    #[Test]
    public function status_change_to_deployed_creates_notification(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'status_code' => 7,
        ]);

        event(new ApplicantStatusChanged($applicant, 7, 8, $this->staffUser));

        $notification = Notification::where('type', 'status_change')->first();

        $this->assertNotNull($notification);
        $this->assertEquals(8, $notification->data['to_status'] ?? $notification->data['new_status_code'] ?? null);
    }

    // ─── AGENCY APPROVAL / REJECTION → NOTIFICATION ───────────────────

    #[Test]
    public function agency_approved_creates_notification(): void
    {
        $pendingAgency = Agency::factory()->create(['status' => 'pending']);

        event(new AgencyApproved($pendingAgency));

        $this->assertDatabaseHas('notifications', [
            'type' => 'approval',
        ]);
    }

    #[Test]
    public function agency_approved_notification_targets_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'super_admin',
        ]);
        $pendingAgency = Agency::factory()->create(['status' => 'pending']);

        event(new AgencyApproved($pendingAgency));

        $notification = Notification::where('type', 'approval')->first();

        $this->assertNotNull($notification);
        $this->assertEquals($superAdmin->id, $notification->user_id);
    }

    #[Test]
    public function agency_rejected_creates_notification(): void
    {
        $pendingAgency = Agency::factory()->create(['status' => 'pending']);

        event(new AgencyRejected($pendingAgency));

        $this->assertDatabaseHas('notifications', [
            'type' => 'approval',
        ]);
    }

    #[Test]
    public function agency_approval_notification_contains_agency_info(): void
    {
        $pendingAgency = Agency::factory()->create([
            'status' => 'pending',
            'name'   => 'Test Agency Inc.',
        ]);

        event(new AgencyApproved($pendingAgency));

        $notification = Notification::where('type', 'approval')->first();

        $this->assertNotNull($notification);
        $this->assertArrayHasKey('agency_name', $notification->data);
        $this->assertArrayHasKey('action', $notification->data);
        $this->assertEquals('approved', $notification->data['action']);
    }

    // ─── BILL CREATION → NOTIFICATION ─────────────────────────────────

    #[Test]
    public function bill_created_creates_notification(): void
    {
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        event(new BillCreated($bill));

        $this->assertDatabaseHas('notifications', [
            'type' => 'bill_due',
        ]);
    }

    #[Test]
    public function bill_created_notification_targets_billing_user(): void
    {
        $billingUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        event(new BillCreated($bill));

        $notification = Notification::where('type', 'bill_due')->first();

        $this->assertNotNull($notification);
        $this->assertEquals($billingUser->id, $notification->user_id);
    }

    #[Test]
    public function bill_created_notification_contains_bill_info(): void
    {
        $bill = Bill::factory()->create([
            'agency_id'      => $this->agency->id,
            'employer_cost'  => 5000.00,
            'applicant_cost' => 2000.00,
        ]);

        event(new BillCreated($bill));

        $notification = Notification::where('type', 'bill_due')->first();

        $this->assertNotNull($notification);
        $this->assertArrayHasKey('bill_id', $notification->data);
        $this->assertArrayHasKey('total_amount', $notification->data);
    }

    // ─── DOCUMENT APPROVAL / REJECTION → NOTIFICATION ─────────────────

    #[Test]
    public function document_approved_creates_notification(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        event(new DocumentApproved($applicant, 'passport', $this->adminUser));

        $this->assertDatabaseHas('notifications', [
            'type' => 'document_approved',
        ]);
    }

    #[Test]
    public function document_rejected_creates_notification(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        event(new DocumentRejected($applicant, 'passport', 'Invalid photo', $this->adminUser));

        $this->assertDatabaseHas('notifications', [
            'type' => 'document_rejected',
        ]);
    }

    #[Test]
    public function document_approval_notification_contains_document_type(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Maria',
            'last_name'  => 'Santos',
        ]);

        event(new DocumentApproved($applicant, 'passport', $this->adminUser));

        $notification = Notification::where('type', 'document_approved')->first();

        $this->assertNotNull($notification);
        $this->assertArrayHasKey('document_type', $notification->data);
        $this->assertEquals('passport', $notification->data['document_type']);
        $this->assertArrayHasKey('applicant_name', $notification->data);
    }

    #[Test]
    public function document_rejection_notification_contains_reason(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        event(new DocumentRejected($applicant, 'passport', 'Blurry photo', $this->adminUser));

        $notification = Notification::where('type', 'document_rejected')->first();

        $this->assertNotNull($notification);
        $this->assertArrayHasKey('reason', $notification->data);
        $this->assertEquals('Blurry photo', $notification->data['reason']);
    }

    // ─── NOTIFICATION IS PERSISTED ────────────────────────────────────

    #[Test]
    public function dispatched_event_persists_notification_to_database(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        event(new ApplicantStatusChanged($applicant, 0, 1, $this->staffUser));

        $this->assertDatabaseHas('notifications', [
            'agency_id' => $this->agency->id,
            'user_id'   => $this->staffUser->id,
            'type'      => 'status_change',
        ]);

        $this->assertEquals(1, Notification::where('type', 'status_change')->count());
    }

    // ─── EVENT-TO-LISTENER MAPPING IN PROVIDER ────────────────────────

    #[Test]
    public function event_service_provider_maps_events_to_listeners(): void
    {
        $provider = $this->app->getProvider(\App\Providers\EventServiceProvider::class);

        $this->assertNotNull($provider, 'EventServiceProvider must be registered');

        $listen = $provider->listen ?? [];

        $this->assertArrayHasKey(ApplicantStatusChanged::class, $listen);
        $this->assertContains(SendStatusChangeNotification::class, $listen[ApplicantStatusChanged::class]);
    }
}
