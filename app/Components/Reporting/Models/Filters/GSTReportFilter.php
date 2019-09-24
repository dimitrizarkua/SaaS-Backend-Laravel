<?php

namespace App\Components\Reporting\Models\Filters;

use App\Models\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class GSTReportFilter
 *
 * @package App\Components\Reporting\Models\Filters
 */
class GSTReportFilter extends Filter
{
    /**
     * @var int
     */
    public $location_id;

    /**
     * @var Carbon
     */
    private $date_from;

    /**
     * @var Carbon
     */
    private $date_to;

    /**
     * @param string $dateFrom
     *
     * @return GSTReportFilter
     */
    public function setDateFrom(string $dateFrom): self
    {
        $this->date_from = new Carbon($dateFrom);

        return $this;
    }

    /**
     * @param string $dateTo
     *
     * @return GSTReportFilter
     */
    public function setDateTo(string $dateTo): self
    {
        $this->date_to = new Carbon($dateTo);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function apply(Builder $query): Builder
    {
        return $query
            ->where('location_id', $this->location_id)
            ->where('date', '>=', $this->date_from)
            ->where('date', '<=', $this->date_to);
    }
}
