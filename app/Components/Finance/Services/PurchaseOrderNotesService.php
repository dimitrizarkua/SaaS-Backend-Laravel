<?php

namespace App\Components\Finance\Services;

use App\Components\Finance\Events\NoteAttachedToPurchaseOrder;
use App\Components\Finance\Interfaces\PurchaseOrderNotesServiceInterface;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Interfaces\UserNotificationsServiceInterface;
use Exception;

/**
 * Class PurchaseOrderNotesService
 *
 * @package App\Components\Finance\Services
 */
class PurchaseOrderNotesService extends PurchaseOrderEntityService implements PurchaseOrderNotesServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Finance\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function attachNote(int $purchaseOrderId, int $noteId): void
    {
        $purchaseOrder = $this->purchaseOrderService()
            ->getEntity($purchaseOrderId);
        /** @var Note $note */
        $note = Note::findOrFail($noteId);

        try {
            $purchaseOrder->notes()->attach($note->id);
        } catch (Exception $exception) {
            throw new NotAllowedException('This note is already attached to specified purchase order.');
        }

        $this->dispatchAddNoteEvents($purchaseOrder, $note);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function detachNote(int $purchaseOrderId, int $noteId): void
    {
        $purchaseOrder = $this->purchaseOrderService()
            ->getEntity($purchaseOrderId);

        $purchaseOrder->notes()->detach($noteId);
    }

    /**
     * @param \App\Components\Finance\Models\PurchaseOrder $purchaseOrder
     * @param \App\Components\Notes\Models\Note            $note
     *
     * @throws \ReflectionException
     */
    private function dispatchAddNoteEvents(PurchaseOrder $purchaseOrder, Note $note): void
    {
        event(new NoteAttachedToPurchaseOrder($purchaseOrder, $note));

        $this->getNotificationService()
            ->dispatchUserMentionedEvent($purchaseOrder, $note, $note->user_id);
    }

    /**
     * @return \App\Components\Notifications\Interfaces\UserNotificationsServiceInterface
     */
    private function getNotificationService(): UserNotificationsServiceInterface
    {
        return app()->make(UserNotificationsServiceInterface::class);
    }
}
