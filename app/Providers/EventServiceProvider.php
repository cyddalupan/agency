<?php

namespace App\Providers;

use App\Events\AgencyApproved;
use App\Events\AgencyRejected;
use App\Events\ApplicantStatusChanged;
use App\Events\BillCreated;
use App\Events\DocumentApproved;
use App\Events\DocumentRejected;
use App\Events\PaymentReceived;
use App\Listeners\SendAgencyApprovalNotification;
use App\Listeners\SendBillCreatedNotification;
use App\Listeners\SendDocumentApprovalNotification;
use App\Listeners\SendDocumentRejectionNotification;
use App\Listeners\SendPaymentReceivedNotification;
use App\Listeners\SendStatusChangeNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Create a new event service provider instance.
     */
    public function __construct(\Illuminate\Contracts\Foundation\Application $app)
    {
        parent::__construct($app);
        \Illuminate\Foundation\Support\Providers\EventServiceProvider::disableEventDiscovery();
    }

    /**
     * The event handler mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    public $listen = [
        ApplicantStatusChanged::class => [
            SendStatusChangeNotification::class,
        ],
        BillCreated::class => [
            SendBillCreatedNotification::class,
        ],
        PaymentReceived::class => [
            SendPaymentReceivedNotification::class,
        ],
        DocumentApproved::class => [
            SendDocumentApprovalNotification::class,
        ],
        DocumentRejected::class => [
            SendDocumentRejectionNotification::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array<int, class-string>
     */
    protected $subscribe = [
        SendAgencyApprovalNotification::class,
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
