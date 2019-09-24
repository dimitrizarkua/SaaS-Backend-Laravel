<?php

namespace App\Components\Finance\Services;

use App\Components\Finance\Interfaces\InvoiceCountersDataProviderInterface;
use App\Components\Finance\Interfaces\InvoiceListingDataProviderInterface;
use App\Components\Finance\Interfaces\InvoiceListingServiceInterface;
use App\Components\Finance\Models\Filters\InvoiceListingFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class InvoiceListingService
 *
 * @package App\Components\Finance\Services
 */
class InvoiceListingService implements InvoiceListingServiceInterface
{
    /**
     * @var InvoiceCountersDataProviderInterface
     */
    private $counterDataProvider;
    /**
     * @var InvoiceListingDataProviderInterface
     */
    private $listingDataProvider;

    /**
     * InvoiceListingService constructor.
     *
     * @param InvoiceCountersDataProviderInterface $counterDataProvider
     * @param InvoiceListingDataProviderInterface  $listingDataProvider
     */
    public function __construct(
        InvoiceCountersDataProviderInterface $counterDataProvider,
        InvoiceListingDataProviderInterface $listingDataProvider
    ) {
        $this->counterDataProvider = $counterDataProvider;
        $this->listingDataProvider = $listingDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function getInvoiceCounters(array $locationIds): array
    {
        return [
            'draft'   => $this->counterDataProvider->getDraftCounter($locationIds)->toArray(),
            'unpaid'  => $this->counterDataProvider->getUnpaidCounter($locationIds)->toArray(),
            'overdue' => $this->counterDataProvider->getOverdueCounter($locationIds)->toArray(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getDraftInvoicesList(InvoiceListingFilter $filter): Collection
    {
        return $this->listingDataProvider->getDraft($filter);
    }

    /**
     * @inheritdoc
     */
    public function getUnpaidInvoicesList(InvoiceListingFilter $filter): Collection
    {
        return $this->listingDataProvider->getUnpaid($filter);
    }

    /**
     * @inheritDoc
     */
    public function getOverdueInvoicesList(InvoiceListingFilter $filter): Collection
    {
        return $this->listingDataProvider->getOverdue($filter);
    }

    /**
     * @inheritDoc
     */
    public function getAllInvoicesList(InvoiceListingFilter $filter): Builder
    {
        return $this->listingDataProvider->getAll($filter);
    }
}
