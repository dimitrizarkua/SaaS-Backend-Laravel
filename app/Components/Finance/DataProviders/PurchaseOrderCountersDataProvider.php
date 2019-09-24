<?php

namespace App\Components\Finance\DataProviders;

use App\Components\Finance\Enums\PurchaseOrderCountersCacheKeys;
use App\Components\Finance\Interfaces\PurchaseOrderCountersDataProviderInterface;
use App\Components\Finance\Interfaces\PurchaseOrderListingDataProviderInterface;
use App\Components\Finance\Models\FinancialEntity;
use App\Components\Finance\Models\VO\CounterItem;

/**
 * Class PurchaseOrderCountersDataProvider
 *
 * @package App\Components\Finance\DataProviders
 */
class PurchaseOrderCountersDataProvider extends CountersDataProvider implements
    PurchaseOrderCountersDataProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \JsonMapper_Exception
     */
    public function getDraftCounters(array $locationIds): CounterItem
    {
        return $this->getCounters(PurchaseOrderCountersCacheKeys::CACHE_TYPE_DRAFT, $locationIds);
    }

    /**
     * @inheritDoc
     *
     * @throws \JsonMapper_Exception
     */
    public function getPendingApprovalCounters(array $locationIds): CounterItem
    {
        return $this->getCounters(PurchaseOrderCountersCacheKeys::CACHE_TYPE_PENDING, $locationIds);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \JsonMapper_Exception
     */
    public function getApprovedCounters(array $locationIds): CounterItem
    {
        return $this->getCounters(PurchaseOrderCountersCacheKeys::CACHE_TYPE_APPROVED, $locationIds);
    }

    /**
     * @inheritDoc
     */
    protected function getCounterKeyFormat(): string
    {
        return PurchaseOrderCountersCacheKeys::COUNTER_KEY_FORMAT;
    }

    /**
     * @inheritDoc
     */
    protected function getAmountKeyFormat(): string
    {
        return PurchaseOrderCountersCacheKeys::AMOUNT_KEY_FORMAT;
    }

    /**
     * @inheritDoc
     */
    protected function getListingDataProviderClassName(): string
    {
        return PurchaseOrderListingDataProviderInterface::class;
    }

    /**
     * @inheritDoc
     */
    protected function getTypeMethodMap(): array
    {
        return [
            PurchaseOrderCountersCacheKeys::CACHE_TYPE_DRAFT    => 'getDraft',
            PurchaseOrderCountersCacheKeys::CACHE_TYPE_PENDING  => 'getPendingApproval',
            PurchaseOrderCountersCacheKeys::CACHE_TYPE_APPROVED => 'getApproved',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getAmountValue(FinancialEntity $entity): float
    {
        return $entity->getTotalAmount();
    }
}
