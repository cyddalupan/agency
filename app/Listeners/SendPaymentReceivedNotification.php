<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Models\Notification;
use App\Models\User;

class SendPaymentReceivedNotification
{
    /**
     * Handle the event.
     */
    public function handle(PaymentReceived $event): void
    {
        $bill = $event->bill;
        $payment = $event->payment;

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
            'type'      => 'payment_received',
            'data'      => [
                'bill_id'    => $bill->id,
                'amount'     => $payment->amount,
                'payment_id' => $payment->id,
            ],
        ]);
    }
}
