<?php

namespace App\Components\Finance\DataProviders;

use App\Components\Finance\Interfaces\CreditNoteCountersDataProviderInterface;
use App\Components\Finance\Interfaces\CreditNoteListingDataProviderInterface;
use App\Components\Finance\Models\FinancialEntity;
use App\Components\Finance\Models\VO\CounterItem;

/**
 * Class CreditNoteCountersDataProvider
 *
 * @package App\Components\Finance\DataProviders
 */
class CreditNoteCountersDataProvider extends CountersDataProvider implements CreditNoteCountersDataProviderInterface
{
    /**
     * Types of allowed cache type
     */
    private const CACHE_TYPE_DRAFT            = 'draft';
    private const CACHE_TYPE_PENDING_APPROVAL = 'pending_approval';
    private const CACHE_TYPE_APPROVED         = 'approved';

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
    public function getPendingApprovalCounters(array $locationIds): CounterItem
    {
        return $this->getCounters(self::CACHE_TYPE_PENDING_APPROVAL, $locationIds);
    }

    /**
     * @inheritDoc
     *
     * @throws \JsonMapper_Exception
     */
    public function getApprovedCounters(array $locationIds): CounterItem
    {
        return $this->getCounters(self::CACHE_TYPE_APPROVED, $locationIds);
    }

    /**
     * @inheritDoc
     */
    protected function getCounterKeyFormat(): string
    {
        return 'credit_notes:counter:%s:%d';
    }

    /**
     * @inheritDoc
     */
    protected function getAmountKeyFormat(): string
    {
        return 'credit_notes:amount:%s:%d';
    }

    /**
     * @inheritDoc
     */
    protected function getListingDataProviderClassName(): string
    {
        return CreditNoteListingDataProviderInterface::class;
    }

    /**
     * @inheritDoc
     */
    protected function getTypeMethodMap(): array
    {
        return [
            self::CACHE_TYPE_DRAFT            => 'getDraft',
            self::CACHE_TYPE_PENDING_APPROVAL => 'getPendingApproval',
            self::CACHE_TYPE_APPROVED         => 'getApproved',
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
