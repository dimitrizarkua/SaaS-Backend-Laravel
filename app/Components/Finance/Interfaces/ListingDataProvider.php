<?php

namespace App\Components\Finance\Interfaces;

use App\Models\Filter;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

/**
 * Interface ListingDataProvider
 *
 * @package App\Components\Finance\Interfaces
 */
interface ListingDataProvider
{
    /**
     * Returns collection of draft entities relevant for given locations.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Support\Collection|\App\Components\Finance\Models\Invoice[]
     */
    public function getDraft(Filter $filter): Collection;

    /**
     * Returns query that returns all entities relevant for given locations.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getAll(Filter $filter): Builder;
}
