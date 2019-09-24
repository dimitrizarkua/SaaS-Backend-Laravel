<?php

namespace App\Components\Reporting\Models\Filters;

/**
 * Class FinancialReportFilter
 *
 * @package App\Components\Reporting\Models\VO
 */
class FinancialReportFilter extends ReportFilter
{
    /**
     * @var int|null
     */
    public $gl_account_id;

    /**
     * @var array|null Job identifiers for chosen period.
     */
    public $job_ids = [];
}
