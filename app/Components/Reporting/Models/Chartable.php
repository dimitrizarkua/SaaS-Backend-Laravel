<?php

namespace App\Components\Reporting\Models;

use App\Components\Reporting\Models\Filters\ReportFilter;
use Illuminate\Support\Collection;

/**
 * Trait Chartable
 *
 * @package App\Components\Reporting\Models
 */
trait Chartable
{
    /**
     * Returns chart for given date interval and list of entities.
     *
     * @param ReportFilter $filter    Options to filter.
     * @param Collection   $entities  List of selected entities grouped by date.
     * @param string       $attribute Name of summable attribute.
     *
     * @return array format: [date, value].
     */
    protected function getChart(ReportFilter $filter, Collection $entities, string $attribute): array
    {
        $date             = clone $filter->date_from;
        $valuesForPeriods = [];

        while ($date->lte($filter->date_to)) {
            $valuesForPeriods[] = [
                'date'  => $date->toDateString(),
                'value' => $entities->has($date->toDateString())
                    ? $entities->get($date->toDateString())->sum($attribute)
                    : 0,
            ];
            $date->addDay();
        }

        return $valuesForPeriods;
    }
}
