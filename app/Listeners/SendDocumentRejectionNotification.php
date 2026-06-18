<?php

namespace App\Listeners;

use App\Events\DocumentRejected;
use App\Models\Notification;
use App\Models\User;

class SendDocumentRejectionNotification
{
    /**
     * Handle the event.
     */
    public function handle(DocumentRejected $event): void
    {
        $userId = $event->rejectedBy?->id
            ?? User::where('agency_id', $event->applicant->agency_id)
                ->where('user_type', 'admin')
                ->value('id')
            ?? User::where('agency_id', $event->applicant->agency_id)
                ->value('id')
            ?? User::value('id');

        Notification::create([
            'agency_id' => $event->applicant->agency_id,
            'user_id'   => $userId ?? 1,
            'type'      => 'document_rejected',
            'data'      => [
                'document_type'  => $event->documentType,
                'reason'         => $event->reason,
                'applicant_name' => $event->applicant->first_name . ' ' . $event->applicant->last_name,
            ],
        ]);
    }
}
