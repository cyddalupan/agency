<?php

namespace App\Listeners;

use App\Events\ApplicantStatusChanged;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendStatusChangeNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ApplicantStatusChanged $event): void
    {
        Notification::create([
            'agency_id' => $event->applicant->agency_id,
            'user_id'   => $event->updatedBy->id,
            'type'      => 'status_change',
            'data'      => [
                'applicant_name' => $event->applicant->first_name . ' ' . $event->applicant->last_name,
                'from_status'    => $event->fromStatus,
                'to_status'      => $event->toStatus,
            ],
        ]);
    }
}
