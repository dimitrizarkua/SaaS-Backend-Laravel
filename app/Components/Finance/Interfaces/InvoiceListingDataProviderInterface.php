<?php

namespace App\Components\Finance\Interfaces;

use App\Models\Filter;
use Illuminate\Support\Collection;

/**
 * Interface InvoiceListingDataProviderInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface InvoiceListingDataProviderInterface extends ListingDataProvider
{
    /**
     * Returns collection of approved and unpaid invoices relevant for given locations.
     *
     * @param \App\Models\Filter $filter Filter instance.
     *
     * @return \Illuminate\Support\Collection|\App\Components\Finance\Models\Invoice[]
     */
    public function getUnpaid(Filter $filter): Collection;

    /**
     * Returns collection of overdue invoices relevant for given locations.
     *
     * @param \App\Models\Filter $filter Filter instance.
     *
     * @return \Illuminate\Support\Collection|\App\Components\Finance\Models\Invoice[]
     */
    public function getOverdue(Filter $filter): Collection;

    /**
     * Returns list of approved invoices.
     *
     * @param Filter $filter Filter instance.
     *
     * @return Collection
     */
    public function getApproved(Filter $filter): Collection;
}
