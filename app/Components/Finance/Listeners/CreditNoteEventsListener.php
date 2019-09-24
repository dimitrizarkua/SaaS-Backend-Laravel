<?php

namespace App\Components\Finance\Listeners;

use App\Components\Finance\Events\AddApproveRequestsToCreditNote;
use App\Components\Finance\Events\CreditNoteApproved;
use App\Components\Finance\Events\CreditNoteCreated;
use App\Components\Finance\Events\CreditNoteDeleted;
use App\Components\Finance\Events\CreditNoteItemCreated;
use App\Components\Finance\Events\CreditNoteItemDeleted;
use App\Components\Finance\Events\CreditNoteItemUpdated;
use App\Components\Finance\Events\CreditNoteUpdated;
use App\Components\Finance\Events\NoteAttachedToCreditNote;
use App\Components\Finance\Interfaces\CreditNoteCountersDataProviderInterface;
use App\Components\Finance\Services\CreditNoteService;
use App\Jobs\Finance\GeneratePDFForFinancialEntity;
use App\Jobs\Finance\RecalculateCounters;
use App\Jobs\Notifications\SendNotification;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Class CreditNoteEventsListener
 *
 * @package App\Components\Finance\Listeners
 */
class CreditNoteEventsListener
{
    private $service;

    /**
     * CreditNoteEventsListener constructor.
     *
     * @param CreditNoteService $creditNoteService
     */
    public function __construct(CreditNoteService $creditNoteService)
    {
        $this->service = $creditNoteService;
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            CreditNoteCreated::class,
            self::class . '@onCreditNoteCreated'
        );
        $events->listen(
            CreditNoteUpdated::class,
            self::class . '@onCreditNoteUpdated'
        );
        $events->listen(
            CreditNoteDeleted::class,
            self::class . '@onCreditNoteDeleted'
        );
        $events->listen(
            CreditNoteItemCreated::class,
            self::class . '@onCreditNoteItemCreated'
        );
        $events->listen(
            CreditNoteItemUpdated::class,
            self::class . '@onCreditNoteItemUpdated'
        );
        $events->listen(
            CreditNoteItemDeleted::class,
            self::class . '@onCreditNoteItemDeleted'
        );
        $events->listen(
            AddApproveRequestsToCreditNote::class,
            self::class . '@onAddedApproveRequestToCreditNote'
        );
        $events->listen(
            CreditNoteApproved::class,
            self::class . '@onCreditNoteApproved'
        );
        $events->listen(
            NoteAttachedToCreditNote::class,
            self::class . '@onNoteAttachedToCreditNote'
        );
    }

    /**
     * @param \App\Components\Finance\Events\CreditNoteCreated $event
     */
    public function onCreditNoteCreated(CreditNoteCreated $event): void
    {
        $this->recalculateCounters([$event->creditNote->location_id]);
        $this->generateDocument($event->creditNote->id);
    }

    /**
     * @param \App\Components\Finance\Events\CreditNoteUpdated $event
     */
    public function onCreditNoteUpdated(CreditNoteUpdated $event): void
    {
        $this->recalculateCounters([$event->creditNote->location_id]);
        $this->generateDocument($event->creditNote->id);
    }

    /**
     * @param \App\Components\Finance\Events\CreditNoteDeleted $event
     */
    public function onCreditNoteDeleted(CreditNoteDeleted $event): void
    {
        $this->recalculateCounters([$event->creditNote->location_id]);
    }

    /**
     * @param \App\Components\Finance\Events\CreditNoteItemCreated $event
     */
    public function onCreditNoteItemCreated(CreditNoteItemCreated $event): void
    {
        $this->recalculateCounters([$event->creditNote->location_id]);
        $this->generateDocument($event->creditNote->id);
    }

    /**
     * @param \App\Components\Finance\Events\CreditNoteItemUpdated $event
     */
    public function onCreditNoteItemUpdated(CreditNoteItemUpdated $event): void
    {
        $this->recalculateCounters([$event->creditNote->location_id]);
        $this->generateDocument($event->creditNote->id);
    }

    /**
     * @param \App\Components\Finance\Events\CreditNoteItemDeleted $event
     */
    public function onCreditNoteItemDeleted(CreditNoteItemDeleted $event): void
    {
        $this->recalculateCounters([$event->creditNote->location_id]);
        $this->generateDocument($event->creditNote->id);
    }

    /**
     * @param \App\Components\Finance\Events\AddApproveRequestsToCreditNote $event
     */
    public function onAddedApproveRequestToCreditNote(AddApproveRequestsToCreditNote $event): void
    {
        $this->recalculateCounters([$event->targetModel->location_id]);
        SendNotification::dispatch($event)
            ->onQueue('notifications');
    }

    /**
     * @param \App\Components\Finance\Events\CreditNoteApproved $event
     */
    public function onCreditNoteApproved(CreditNoteApproved $event): void
    {
        $this->recalculateCounters([$event->creditNote->location_id]);
        $this->generateDocument($event->creditNote->id);
    }

    /**
     * @param \App\Components\Finance\Events\NoteAttachedToCreditNote $event
     */
    public function onNoteAttachedToCreditNote(NoteAttachedToCreditNote $event): void
    {
        SendNotification::dispatch($event)
            ->onQueue('notifications');
    }

    /**
     * @param int $creditNoteId
     */
    private function generateDocument(int $creditNoteId)
    {
        $service = app()->make(CreditNoteService::class);
        GeneratePDFForFinancialEntity::dispatch($service, $creditNoteId);
    }

    /**
     * @param array $locationIds
     */
    private function recalculateCounters(array $locationIds): void
    {
        $dataProvider = app()->make(CreditNoteCountersDataProviderInterface::class);
        RecalculateCounters::dispatch($dataProvider, $locationIds);
    }
}
