<?php

namespace App\Components\Finance\Interfaces;

use App\Models\Filter;
use Illuminate\Support\Collection;

/**
 * Interface CreditNoteListingDataProviderInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface CreditNoteListingDataProviderInterface extends ListingDataProvider
{
    /**
     * Returns collection of pending approval credit notes relevant for given filter.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Support\Collection|\App\Components\Finance\Models\CreditNote[]
     */
    public function getPendingApproval(Filter $filter): Collection;

    /**
     * Returns collection of approved credit notes relevant for given filter.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Support\Collection|\App\Components\Finance\Models\CreditNote[]
     */
    public function getApproved(Filter $filter): Collection;
}
