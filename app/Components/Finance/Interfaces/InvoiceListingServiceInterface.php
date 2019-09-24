<?php

namespace App\Components\Finance\Interfaces;

use App\Components\Finance\Models\Filters\InvoiceListingFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Interface InvoiceListingServiceInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface InvoiceListingServiceInterface
{
    /**
     * Returns counters for invoices.
     *
     * @param array $locationIds Array of location ids.
     *
     * @return array
     */
    public function getInvoiceCounters(array $locationIds): array;

    /**
     * Returns collection of draft invoices relevant for locations to which given user belongs.
     *
     * @param InvoiceListingFilter $filter Filter instance.
     *
     * @return Collection
     */
    public function getDraftInvoicesList(InvoiceListingFilter $filter): Collection;

    /**
     * Returns collection of approved and unpaid invoices.
     *
     * @param InvoiceListingFilter $filter Filter instance.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getUnpaidInvoicesList(InvoiceListingFilter $filter): Collection;

    /**
     * Returns collection of overdue invoices.
     *
     * @param InvoiceListingFilter $filter Filter instance.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOverdueInvoicesList(InvoiceListingFilter $filter): Collection;

    /**
     * Returns query that returns all invoices relevant for locations to which given user belongs.
     *
     * @param InvoiceListingFilter $filter Filter instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getAllInvoicesList(InvoiceListingFilter $filter): Builder;
}
