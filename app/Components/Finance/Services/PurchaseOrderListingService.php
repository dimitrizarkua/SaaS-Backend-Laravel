<?php

namespace App\Components\Finance\Services;

use App\Components\Finance\Interfaces\PurchaseOrderCountersDataProviderInterface;
use App\Components\Finance\Interfaces\PurchaseOrderInfoInterface;
use App\Components\Finance\Interfaces\PurchaseOrderListingDataProviderInterface;
use App\Components\Finance\Interfaces\PurchaseOrderListingServiceInterface;
use App\Components\Finance\Models\VO\PurchaseOrderInfo;
use App\Models\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class PurchaseOrderListingService
 *
 * @package App\Components\Finance\Services
 */
class PurchaseOrderListingService implements PurchaseOrderListingServiceInterface
{
    /**
     * @var PurchaseOrderListingDataProviderInterface
     */
    private $listingDataProvider;

    /**
     * @var PurchaseOrderCountersDataProviderInterface
     */
    private $countersDataProvider;

    /**
     * PurchaseOrderListingService constructor.
     *
     * @param PurchaseOrderListingDataProviderInterface  $listingDataProvider
     * @param PurchaseOrderCountersDataProviderInterface $countersDataProvider
     */
    public function __construct(
        PurchaseOrderListingDataProviderInterface $listingDataProvider,
        PurchaseOrderCountersDataProviderInterface $countersDataProvider
    ) {
        $this->listingDataProvider  = $listingDataProvider;
        $this->countersDataProvider = $countersDataProvider;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \JsonMapper_Exception
     */
    public function getInfo(array $locationIds): PurchaseOrderInfoInterface
    {
        $data = [
            'draft_counter'            => $this->countersDataProvider->getDraftCounters($locationIds),
            'pending_approval_counter' => $this->countersDataProvider->getPendingApprovalCounters($locationIds),
            'approved_counter'         => $this->countersDataProvider->getApprovedCounters($locationIds),
        ];

        return PurchaseOrderInfo::createFromJson($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getDraftPurchaseOrders(Filter $filter): Collection
    {
        return $this->listingDataProvider->getDraft($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingApprovalPurchaseOrders(Filter $filter): Collection
    {
        return $this->listingDataProvider->getPendingApproval($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getApprovedPurchaseOrders(Filter $filter): Collection
    {
        return $this->listingDataProvider->getApproved($filter);
    }

    /**
     * @inheritDoc
     */
    public function getAll(Filter $filter): Builder
    {
        return $this->listingDataProvider->getAll($filter);
    }
}
