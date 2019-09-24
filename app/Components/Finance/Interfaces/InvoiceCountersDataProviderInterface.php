<?php

namespace App\Components\Finance\Interfaces;

use App\Components\Finance\Models\VO\CounterItem;

/**
 * Interface InvoiceCountersDataProviderInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface InvoiceCountersDataProviderInterface
{
    /**
     * @param array $locationIds Array of location ids.
     *
     * @return \App\Components\Finance\Models\VO\CounterItem
     */
    public function getDraftCounter(array $locationIds): CounterItem;

    /**
     * @param array $locationIds Array of location ids.
     *
     * @return \App\Components\Finance\Models\VO\CounterItem
     */
    public function getUnpaidCounter(array $locationIds): CounterItem;

    /**
     * @param array $locationIds Array of location ids.
     *
     * @return \App\Components\Finance\Models\VO\CounterItem
     */
    public function getOverdueCounter(array $locationIds): CounterItem;
}
