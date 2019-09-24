<?php

namespace App\Components\Finance\Interfaces;

use App\Components\Finance\Models\VO\CounterItem;

/**
 * Interface CreditNoteCountersDataProviderInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface CreditNoteCountersDataProviderInterface
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
    public function getPendingApprovalCounters(array $locationIds): CounterItem;

    /**
     * @param array $locationIds Array of location ids.
     *
     * @return \App\Components\Finance\Models\VO\CounterItem
     */
    public function getApprovedCounters(array $locationIds): CounterItem;
}
