<?php

namespace App\Components\Finance\Interfaces;

use App\Models\Filter;
use Illuminate\Support\Collection;

/**
 * Interface PurchaseOrderListingDataProviderInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface PurchaseOrderListingDataProviderInterface extends ListingDataProvider
{
    /**
     * Returns collection of pending approval purchase orders relevant for given filter.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Support\Collection|\App\Components\Finance\Models\PurchaseOrder[]
     */
    public function getPendingApproval(Filter $filter): Collection;

    /**
     * Returns collection of approved purchase orders relevant for given filter.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Support\Collection|\App\Components\Finance\Models\PurchaseOrder[]
     */
    public function getApproved(Filter $filter): Collection;
}
