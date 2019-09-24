<?php

namespace App\Components\Finance\Listeners;

use App\Components\Finance\Events\AddApproveRequestsToPurchaseOrder;
use App\Components\Finance\Events\NoteAttachedToPurchaseOrder;
use App\Components\Finance\Events\PurchaseOrderCreated;
use App\Components\Finance\Events\PurchaseOrderDeleted;
use App\Components\Finance\Events\PurchaseOrderItemsUpdated;
use App\Components\Finance\Events\PurchaseOrderStatusChanged;
use App\Components\Finance\Events\PurchaseOrderUpdated;
use App\Components\Finance\Interfaces\PurchaseOrderCountersDataProviderInterface;
use App\Components\Finance\Services\PurchaseOrdersService;
use App\Jobs\Finance\GeneratePDFForFinancialEntity;
use App\Jobs\Finance\RecalculateCounters;
use App\Jobs\Notifications\SendNotification;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\App;

/**
 * Class PurchaseOrderEventsListener
 *
 * @package Components\Finance\Listeners
 */
class PurchaseOrderEventsListener
{
    /**
     * @var array
     */
    protected $events = [
        NoteAttachedToPurchaseOrder::class       => '@onNoteAttachedToPurchaseOrder',
        PurchaseOrderCreated::class              => '@onPurchaseOrderTouched',
        PurchaseOrderUpdated::class              => '@onPurchaseOrderUpdated',
        PurchaseOrderDeleted::class              => '@onPurchaseOrderTouched',
        PurchaseOrderStatusChanged::class        => '@onPurchaseOrderTouched',
        PurchaseOrderItemsUpdated::class         => '@onPurchaseOrderTouched',
        AddApproveRequestsToPurchaseOrder::class => '@onAddedApproveRequestToPurchaseOrder',
    ];

    /**
     * @param Dispatcher $dispatcher
     */
    public function subscribe(Dispatcher $dispatcher)
    {
        if (App::environment('testing')) {
            return;
        }

        foreach ($this->events as $eventClassName => $method) {
            $dispatcher->listen($eventClassName, self::class . $method);
        }
    }

    /**
     * @param \App\Components\Finance\Events\NoteAttachedToPurchaseOrder $event
     */
    public function onNoteAttachedToPurchaseOrder(NoteAttachedToPurchaseOrder $event): void
    {
        SendNotification::dispatch($event)
            ->onQueue('notifications');
    }

    /**
     * @param $event
     */
    public function onPurchaseOrderTouched($event): void
    {
        $this->recalculateCounters([$event->purchaseOrder->location_id]);
        $this->generateDocument($event->purchaseOrder->id);
    }

    /**
     * @param $event
     */
    public function onAddedApproveRequestToPurchaseOrder($event): void
    {
        $this->recalculateCounters([$event->targetModel->location_id]);
        $this->generateDocument($event->targetModel->id);
        SendNotification::dispatch($event)
            ->onQueue('notifications');
    }

    /**
     * @param PurchaseOrderUpdated $event
     */
    public function onPurchaseOrderUpdated(PurchaseOrderUpdated $event): void
    {
        if ($event->locationId) {
            $this->recalculateCounters([$event->purchaseOrder->location_id, $event->locationId]);
        }

        if ($event->generatePDF) {
            $this->generateDocument($event->purchaseOrder->id);
        }
    }

    /**
     * @param array $locationIds
     */
    private function recalculateCounters(array $locationIds): void
    {
        $dataProvider = app()->make(PurchaseOrderCountersDataProviderInterface::class);
        RecalculateCounters::dispatch($dataProvider, $locationIds);
    }

    private function generateDocument(int $purchaseOrderId)
    {
        $service = app()->make(PurchaseOrdersService::class);
        GeneratePDFForFinancialEntity::dispatch($service, $purchaseOrderId);
    }
}
