<?php

namespace App\Models;

use App\Core\JsonModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Filter
 *
 * @package App\Models
 */
abstract class Filter extends JsonModel
{
    /**
     * Apply filter to query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract public function apply(Builder $query): Builder;
}
