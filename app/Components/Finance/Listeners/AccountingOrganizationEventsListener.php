<?php

namespace App\Components\Finance\Listeners;

use App\Components\Finance\Events\AccountingOrganizationCreated;
use App\Jobs\Finance\CreateDefaultGLAccounts;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Class AccountingOrganizationEventsListener
 *
 * @package App\Components\Finance\Listener
 */
class AccountingOrganizationEventsListener
{
    /**
     * @param Dispatcher $dispatcher
     */
    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen(
            AccountingOrganizationCreated::class,
            self::class . '@onAccountingOrganizationCreated'
        );
    }

    /**
     * @param $event
     */
    public function onAccountingOrganizationCreated(AccountingOrganizationCreated $event): void
    {
        CreateDefaultGLAccounts::dispatch($event->accountingOrganization->id);
    }
}
