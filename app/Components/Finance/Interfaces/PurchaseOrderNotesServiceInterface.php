<?php

namespace App\Components\Finance\Interfaces;

/**
 * Interface PurchaseOrderNotesServiceInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface PurchaseOrderNotesServiceInterface
{
    /**
     * Allows to attach a note to a purchase order.
     *
     * @param int $purchaseOrderId Purchase order id.
     * @param int $noteId          Note id.
     *
     * @return void
     */
    public function attachNote(int $purchaseOrderId, int $noteId): void;

    /**
     * Allows to detach a note from a purchase order.
     *
     * @param int $purchaseOrderId Purchase order id.
     * @param int $noteId          Note id.
     *
     * @return void
     */
    public function detachNote(int $purchaseOrderId, int $noteId): void;
}
