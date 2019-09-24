<?php

namespace App\Components\Finance\DataProviders;

use App\Components\Finance\Interfaces\ListingDataProvider;
use App\Components\Finance\Models\Filters\InvoiceListingFilter;
use App\Components\Finance\Models\FinancialEntity;
use App\Components\Finance\Models\VO\CounterItem;
use Illuminate\Support\Facades\Cache;

/**
 * Class CountersDataProvider
 *
 * @package App\Components\Finance\DataProviders
 */
abstract class CountersDataProvider
{
    /**
     * Returns format of counter key.
     *
     * @return string
     */
    abstract protected function getCounterKeyFormat(): string;

    /**
     * Returns format of amount key.
     *
     * @return string
     */
    abstract protected function getAmountKeyFormat(): string;

    /**
     * Returns class name of listing data provider.
     *
     * @return string
     */
    abstract protected function getListingDataProviderClassName(): string;

    /**
     * Returns map of cache type to listing data provider.
     *
     * @return array
     */
    abstract protected function getTypeMethodMap(): array;

    /**
     * Returns param name for which sum amount should be calculated.
     *
     * @return callable
     */
    abstract protected function getAmountValue(FinancialEntity $entity): float;

    /**
     * Recalculate all counters for given location.
     *
     * @param array $locationIds Array of location id.
     *
     * @throws \JsonMapper_Exception
     */
    public function recalculateCounters(array $locationIds): void
    {
        $types = array_keys($this->getTypeMethodMap());
        foreach ($types as $type) {
            $this->getCountersAndSetToCache($type, $locationIds);
        }
    }

    /**
     * Returns formatted counter key.
     *
     * @param string $type       Cache type.
     * @param int    $locationId Location id.
     *
     * @return string
     */
    protected function getCounterKey(string $type, int $locationId): string
    {
        return sprintf($this->getCounterKeyFormat(), $type, $locationId);
    }

    /**
     * Returns formatted amount key.
     *
     * @param string $type       Cache type.
     * @param int    $locationId Location id.
     *
     * @return string
     */
    protected function getAmountKey(string $type, int $locationId): string
    {
        return sprintf($this->getAmountKeyFormat(), $type, $locationId);
    }

    /**
     * Returns listing data provider instance.
     *
     * @return ListingDataProvider
     */
    protected function getListingDataProvider(): ListingDataProvider
    {
        return app()->make($this->getListingDataProviderClassName());
    }

    /**
     * Returns counters (from cache or database).
     *
     * @param string $type        Cache type.
     * @param array  $locationIds Array of location ids.
     *
     * @throws \JsonMapper_Exception
     *
     * @return CounterItem
     */
    protected function getCounters(string $type, array $locationIds): CounterItem
    {
        if (false === $this->hasCache($type, $locationIds)) {
            return $this->getCountersAndSetToCache($type, $locationIds);
        }

        $counterItem = new CounterItem();
        foreach ($locationIds as $locationId) {
            $countKey            = $this->getCounterKey($type, $locationId);
            $amountKey           = $this->getAmountKey($type, $locationId);
            $counterItem->count  += Cache::get($countKey);
            $counterItem->amount += Cache::get($amountKey);
        }

        return $counterItem;
    }

    /**
     * Checks whether is cache isset for give locations and given type.
     * Note: if there is no at least one counter method returns false.
     *
     * @param string $type
     * @param array  $locationIds
     *
     * @return bool
     */
    protected function hasCache(string $type, array $locationIds): bool
    {
        foreach ($locationIds as $locationId) {
            $countKey  = $this->getCounterKey($type, $locationId);
            $amountKey = $this->getAmountKey($type, $locationId);

            if (false === Cache::has($countKey) || false === Cache::has($amountKey)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieves data from the database and store counters into cache.
     *
     * @param string $type        Cache type.
     * @param array  $locationIds Array of location ids.
     *
     * @throws \JsonMapper_Exception
     * @return \App\Components\Finance\Models\VO\CounterItem
     */
    protected function getCountersAndSetToCache(string $type, array $locationIds): CounterItem
    {
        $dataSourceMethodName = $this->getDataSourceMethodName($type);

        /** @var \Illuminate\Support\Collection|\App\Components\Finance\Models\Invoice[] $data */
        $filter = new InvoiceListingFilter(['locations' => $locationIds]);
        $data   = \call_user_func([$this->getListingDataProvider(), $dataSourceMethodName], $filter);

        $groupedInvoiceList = $data->groupBy('location_id');

        $counterItem = new CounterItem();
        foreach ($locationIds as $locationId) {
            $countKey  = $this->getCounterKey($type, $locationId);
            $amountKey = $this->getAmountKey($type, $locationId);

            if (false === $groupedInvoiceList->has($locationId)) {
                Cache::forever($countKey, 0);
                Cache::forever($amountKey, 0);
                continue;
            }

            /** @var \Illuminate\Support\Collection $entitiesGroup */
            $entitiesGroup = $groupedInvoiceList->get($locationId);
            $count         = $entitiesGroup->count();
            $amount        = $entitiesGroup->reduce(function (float $total, FinancialEntity $entity) {
                return $total + $this->getAmountValue($entity);
            }, 0);

            $counterItem->count  += $count;
            $counterItem->amount += $amount;

            Cache::forever($countKey, $count);
            Cache::forever($amountKey, $amount);
        }

        return $counterItem;
    }

    /**
     * Returns data provider method name.
     *
     * @param string $type Cache type.
     *
     * @return mixed
     */
    protected function getDataSourceMethodName(string $type): string
    {
        $methodMap = $this->getTypeMethodMap();
        if (!array_key_exists($type, $methodMap)) {
            throw new \RuntimeException('There is no method for given type');
        }

        return $methodMap[$type];
    }
}
