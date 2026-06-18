<?php

namespace App\Listeners;

use App\Events\DocumentApproved;
use App\Models\Notification;
use App\Models\User;

class SendDocumentApprovalNotification
{
    /**
     * Handle the event.
     */
    public function handle(DocumentApproved $event): void
    {
        $userId = $event->approvedBy?->id
            ?? User::where('agency_id', $event->applicant->agency_id)
                ->where('user_type', 'admin')
                ->value('id')
            ?? User::where('agency_id', $event->applicant->agency_id)
                ->value('id')
            ?? User::value('id');

        Notification::create([
            'agency_id' => $event->applicant->agency_id,
            'user_id'   => $userId ?? 1,
            'type'      => 'document_approved',
            'data'      => [
                'document_type'  => $event->documentType,
                'applicant_name' => $event->applicant->first_name . ' ' . $event->applicant->last_name,
            ],
        ]);
    }
}
