<?php

namespace App\Components\Finance\DataProviders;

use App\Components\Finance\Interfaces\InvoiceCountersDataProviderInterface;
use App\Components\Finance\Interfaces\InvoiceListingDataProviderInterface;
use App\Components\Finance\Models\FinancialEntity;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\VO\CounterItem;

/**
 * Class InvoiceCountersDataProvider
 *
 * @package App\Components\Finance\DataProviders
 */
class InvoiceCountersDataProvider extends CountersDataProvider implements InvoiceCountersDataProviderInterface
{
    /**
     * Types of allowed cache type
     */
    private const CACHE_TYPE_DRAFT   = 'draft';
    private const CACHE_TYPE_UNPAID  = 'unpaid';
    private const CACHE_TYPE_OVERDUE = 'overdue';

    /**
     * @inheritDoc
     *
     * @throws \JsonMapper_Exception
     */
    public function getDraftCounter(array $locationIds): CounterItem
    {
        return $this->getCounters(self::CACHE_TYPE_DRAFT, $locationIds);
    }

    /**
     * @inheritDoc
     *
     * @throws \JsonMapper_Exception
     */
    public function getUnpaidCounter(array $locationIds): CounterItem
    {
        return $this->getCounters(self::CACHE_TYPE_UNPAID, $locationIds);
    }

    /**
     * @inheritDoc
     *
     * @throws \JsonMapper_Exception
     */
    public function getOverdueCounter(array $locationIds): CounterItem
    {
        return $this->getCounters(self::CACHE_TYPE_OVERDUE, $locationIds);
    }

    /**
     * @inheritDoc
     */
    protected function getCounterKeyFormat(): string
    {
        return 'invoice:counter:%s:%d';
    }

    /**
     * @inheritDoc
     */
    protected function getAmountKeyFormat(): string
    {
        return 'invoice:amount:%s:%d';
    }

    /**
     * @inheritDoc
     */
    protected function getListingDataProviderClassName(): string
    {
        return InvoiceListingDataProviderInterface::class;
    }

    /**
     * @inheritDoc
     */
    protected function getTypeMethodMap(): array
    {
        return [
            self::CACHE_TYPE_DRAFT   => 'getDraft',
            self::CACHE_TYPE_UNPAID  => 'getUnpaid',
            self::CACHE_TYPE_OVERDUE => 'getOverdue',
        ];
    }

    /**
     * @inheritDoc
     *
     * @param FinancialEntity|Invoice $entity
     */
    protected function getAmountValue(FinancialEntity $entity): float
    {
        return $entity->getAmountDue();
    }
}
