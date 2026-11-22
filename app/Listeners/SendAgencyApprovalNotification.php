<?php

namespace App\Listeners;

use App\Events\AgencyApproved;
use App\Events\AgencyRejected;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Events\Dispatcher;

class SendAgencyApprovalNotification
{
    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            AgencyApproved::class,
            [self::class, 'handleAgencyApproved']
        );

        $events->listen(
            AgencyRejected::class,
            [self::class, 'handleAgencyRejected']
        );
    }

    /**
     * Handle AgencyApproved events.
     */
    public function handleAgencyApproved(AgencyApproved $event): void
    {
        $this->createNotification($event->agency, 'approved');
    }

    /**
     * Handle AgencyRejected events.
     */
    public function handleAgencyRejected(AgencyRejected $event): void
    {
        $this->createNotification($event->agency, 'rejected');
    }

    /**
     * Create the notification for agency approval or rejection.
     */
    private function createNotification($agency, string $action): void
    {
        // The target is a super admin: by definition not a tenant read, and
        // an agency-approval event may fire on the control plane before the
        // agency exists. Say so explicitly (TenantScope fails closed on web
        // requests, card 6b008b8dc9020955b2cc16e1).
        $userId = \App\Support\Tenant::bypass(function () {
            return User::where('user_type', 'super_admin')->value('id')
                ?? User::where('user_type', 'admin')->value('id')
                ?? User::value('id');
        }, 'SendAgencyApprovalNotification: find the super admin to notify');

        Notification::create([
            'agency_id' => $agency->id,
            'user_id'   => $userId ?? 1,
            'type'      => 'approval',
            'data'      => [
                'agency_name' => $agency->name,
                'action'      => $action,
                'status'      => $action,
            ],
        ]);
    }
}
