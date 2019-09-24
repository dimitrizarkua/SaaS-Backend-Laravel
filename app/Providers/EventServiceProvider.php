<?php

namespace App\Providers;

use App\Components\AssessmentReports\Listeners\AssessmentReportEventsListener;
use App\Components\Contacts\Listeners\ContactEventsListener;
use App\Components\Finance\Listeners\AccountingOrganizationEventsListener;
use App\Components\Finance\Listeners\CreditNoteEventsListener;
use App\Components\Finance\Listeners\InvoiceEventsListener;
use App\Components\Finance\Listeners\PurchaseOrderEventsListener;
use App\Components\Jobs\Listeners\JobEventsListener;
use App\Components\Jobs\Listeners\JobLastMessageEventsListener;
use App\Components\Notifications\Listeners\UserNotificationEventsListener;
use App\Components\Teams\Listeners\TeamEventsListener;
use App\Mail\MailEventsListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider
 *
 * @package App\Providers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    protected $subscribe = [
        MailEventsListener::class,
        JobEventsListener::class,
        TeamEventsListener::class,
        JobLastMessageEventsListener::class,
        UserNotificationEventsListener::class,
        ContactEventsListener::class,
        PurchaseOrderEventsListener::class,
        InvoiceEventsListener::class,
        CreditNoteEventsListener::class,
        AccountingOrganizationEventsListener::class,
        AssessmentReportEventsListener::class,
    ];
}
