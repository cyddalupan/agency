<?php

namespace App\Listeners;

use App\Events\BillCreated;
use App\Models\Notification;
use App\Models\User;

class SendBillCreatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(BillCreated $event): void
    {
        $bill = $event->bill;

        $userId = User::where('agency_id', $bill->agency_id)
            ->where('user_type', 'billing')
            ->value('id')
            ?? User::where('agency_id', $bill->agency_id)
                ->where('user_type', 'admin')
                ->value('id')
            ?? User::where('agency_id', $bill->agency_id)
                ->value('id')
            ?? User::value('id');

        Notification::create([
            'agency_id' => $bill->agency_id,
            'user_id'   => $userId ?? 1,
            'type'      => 'bill_due',
            'data'      => [
                'bill_id'       => $bill->id,
                'total_amount'  => ($bill->employer_cost ?? 0) + ($bill->applicant_cost ?? 0),
            ],
        ]);
    }
}
