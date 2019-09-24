<?php

namespace App\Components\Finance\Interfaces;

use App\Components\Finance\Models\VO\CounterItem;

/**
 * Interface PurchaseOrderInfoInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface PurchaseOrderInfoInterface
{
    /**
     * Returns purchase orders counter for "Draft" tab.
     *
     * @return CounterItem
     */
    public function getDraftCounter(): CounterItem;

    /**
     * Returns purchase orders counter for "Pending Approval" tab.
     *
     * @return CounterItem
     */
    public function getPendingApprovalCounter(): CounterItem;

    /**
     * Returns purchase orders counter for "Approved" tab.
     *
     * @return CounterItem
     */
    public function getApprovedCounter(): CounterItem;
}
