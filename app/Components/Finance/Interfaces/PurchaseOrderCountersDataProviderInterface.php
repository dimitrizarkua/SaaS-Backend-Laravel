<?php

namespace App\Components\Finance\Interfaces;

use App\Components\Finance\Models\VO\CounterItem;

/**
 * Interface PurchaseOrderCountersDataProviderInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface PurchaseOrderCountersDataProviderInterface
{
    /**
     * @param array $locationIds Array of location ids.
     *
     * @return \App\Components\Finance\Models\VO\CounterItem
     */
    public function getDraftCounters(array $locationIds): CounterItem;

    /**
     * @param array $locationIds Array of location ids.
     *
     * @return \App\Components\Finance\Models\VO\CounterItem
     */
    public function getPendingApprovalCounters(array $locationIds): CounterItem;

    /**
     * @param array $locationIds Array of location ids.
     *
     * @return \App\Components\Finance\Models\VO\CounterItem
     */
    public function getApprovedCounters(array $locationIds): CounterItem;
}
