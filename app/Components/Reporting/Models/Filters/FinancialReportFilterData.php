<?php

namespace App\Components\Reporting\Models\Filters;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Class FinancialReportFilterData
 *
 * @package App\Components\Reporting\Models\VO
 */
class FinancialReportFilterData extends ReportFilterData
{
    /**
     * @var int|null
     */
    public $gl_account_id;

    /**
     * @var array|null Job identifiers for current period.
     */
    private $current_job_ids = [];

    /**
     * @var array|null Job identifiers for previous period.
     */
    private $previous_job_ids = [];

    /**
     * Returns list of job identifier for current filtering options.
     *
     * @return array
     */
    public function getCurrentPeriodJobIds(): array
    {
        return $this->current_job_ids;
    }

    /**
     * Returns list of job identifier for previous filtering options.
     *
     * @return array
     */
    public function getPreviousPeriodJobIds(): array
    {
        return $this->previous_job_ids;
    }

    /**
     * Updates lists of job identifier for given date intervals.
     *
     * @return \App\Components\Reporting\Models\Filters\FinancialReportFilterData
     */
    public function updateJobIds(): self
    {
        $this->current_job_ids  = $this->getJobIds($this->getCurrentDateFrom(), $this->getCurrentDateTo());
        $this->previous_job_ids = $this->getJobIds($this->getPreviousDateFrom(), $this->getPreviousDateTo());

        return $this;
    }

    /**
     * Returns list of job identifier for given date interval.
     *
     * @param \Illuminate\Support\Carbon $dateFrom
     * @param \Illuminate\Support\Carbon $dateTo
     *
     * @return array
     */
    private function getJobIds(Carbon $dateFrom, Carbon $dateTo): array
    {
        return Job::query()
            ->where('assigned_location_id', $this->getLocationId())
            ->whereIn('id', function (QueryBuilder $query) {
                return $query->select('id')
                    ->from(
                        DB::raw('(SELECT jobs.id,
                               (
                                 SELECT status
                                 FROM job_statuses
                                 WHERE job_id = jobs.id
                                 ORDER BY created_at DESC
                                 LIMIT 1
                               ) AS latest_status
                        FROM jobs) subQuery')
                    )
                    ->whereNotIn('latest_status', JobStatuses::$closedStatuses);
            })
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->when($this->tag_ids, function (Builder $query) {
                return $query->whereHas('tags', function (Builder $query) {
                    return $query->whereIn('id', $this->tag_ids);
                });
            })
            ->pluck('id')
            ->toArray();
    }
}
