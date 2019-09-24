<?php

namespace App\Components\Finance\Interfaces;

use App\Components\Finance\Models\PurchaseOrder;
use App\Models\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Interface PurchaseOrderListingServiceInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface PurchaseOrderListingServiceInterface
{
    /**
     * Returns info about lists of purchase orders for specified user.
     *
     * @param array $locationIds Array of location ids.
     *
     * @return \App\Components\Finance\Interfaces\PurchaseOrderInfoInterface
     */
    public function getInfo(array $locationIds): PurchaseOrderInfoInterface;

    /**
     * Returns purchase orders list for the "Draft" tab.
     *
     * @param Filter $filter Filter instance.
     *
     * @return Collection|PurchaseOrder[]
     */
    public function getDraftPurchaseOrders(Filter $filter): Collection;

    /**
     * Returns purchase orders list for the "Pending Approval" tab.
     *
     * @param Filter $filter Filter instance.
     *
     * @return Collection|PurchaseOrder[]
     */
    public function getPendingApprovalPurchaseOrders(Filter $filter): Collection;

    /**
     * Returns purchase orders list for the "Approved" tab.
     *
     * @param Filter $filter Filter instance.
     *
     * @return Collection|PurchaseOrder[]
     */
    public function getApprovedPurchaseOrders(Filter $filter): Collection;

    /**
     * Returns list of all purchase orders.
     *
     * @param \App\Models\Filter $filter Filter instance.
     *
     * @return Builder
     */
    public function getAll(Filter $filter): Builder;
}
