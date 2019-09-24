<?php

namespace App\Components\Finance\Listeners;

use App\Components\Finance\Events\AddApproveRequestsToInvoice;
use App\Components\Finance\Events\InvoiceApproved;
use App\Components\Finance\Events\InvoiceCreated;
use App\Components\Finance\Events\InvoiceDeleted;
use App\Components\Finance\Events\InvoiceItemsUpdated;
use App\Components\Finance\Events\InvoicePaymentCreated;
use App\Components\Finance\Events\InvoiceUpdated;
use App\Components\Finance\Events\NoteAttachedToInvoice;
use App\Components\Finance\Interfaces\InvoiceCountersDataProviderInterface;
use App\Components\Finance\Services\InvoicesService;
use App\Jobs\Finance\GeneratePDFForFinancialEntity;
use App\Jobs\Finance\RecalculateCounters;
use App\Jobs\Notifications\SendNotification;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Class InvoiceEventsListener
 *
 * @package App\Components\Finance\Listener
 */
class InvoiceEventsListener
{
    /**
     * @var array
     */
    protected $events = [
        InvoiceCreated::class              => '@onInvoiceTouched',
        InvoiceUpdated::class              => '@onInvoiceUpdated',
        InvoiceDeleted::class              => '@onInvoiceTouched',
        InvoiceApproved::class             => '@onInvoiceTouched',
        InvoiceItemsUpdated::class         => '@onInvoiceTouched',
        NoteAttachedToInvoice::class       => '@noteAttachedToInvoice',
        AddApproveRequestsToInvoice::class => '@onAddedApproveRequestToInvoice',
        InvoicePaymentCreated::class       => '@onInvoiceTouched',
    ];

    /**
     * @param Dispatcher $dispatcher
     */
    public function subscribe(Dispatcher $dispatcher)
    {
        foreach ($this->events as $event => $handler) {
            $dispatcher->listen($event, self::class . $handler);
        }
    }

    /**
     * @param $event
     */
    public function onInvoiceTouched($event): void
    {
        $this->recalculateCounters([$event->invoice->location_id]);
        $this->generateDocument($event->invoice->id);
    }

    /**
     * @param \App\Components\Finance\Events\InvoiceUpdated $event
     */
    public function onInvoiceUpdated(InvoiceUpdated $event): void
    {
        if ($event->invoice->isDirty(['location_id'])) {
            $oldLocationId = $event->invoice->getOriginal('location_id');
            $newLocationId = $event->invoice->location_id;
            $this->recalculateCounters([$oldLocationId, $newLocationId]);
        }

        //Prevent of loop
        if (!$event->invoice->isDirty('document_id')) {
            $this->generateDocument($event->invoice->id);
        }
    }

    /**
     * @param \App\Components\Finance\Events\AddApproveRequestsToInvoice $event
     */
    public function onAddedApproveRequestToInvoice(AddApproveRequestsToInvoice $event): void
    {
        SendNotification::dispatch($event)
            ->onQueue('notifications');
    }

    /**
     * @param \App\Components\Finance\Events\NoteAttachedToInvoice $event
     */
    public function noteAttachedToInvoice(NoteAttachedToInvoice $event)
    {
        SendNotification::dispatch($event)
            ->onQueue('notifications');
    }

    /**
     * @param array $locationIds
     */
    private function recalculateCounters(array $locationIds): void
    {
        $dataProvider = app()->make(InvoiceCountersDataProviderInterface::class);
        RecalculateCounters::dispatch($dataProvider, $locationIds);
    }

    /**
     * @param int $invoiceId
     */
    private function generateDocument(int $invoiceId)
    {
        $service = app()->make(InvoicesService::class);
        GeneratePDFForFinancialEntity::dispatch($service, $invoiceId);
    }
}
