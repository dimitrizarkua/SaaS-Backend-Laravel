<?php

namespace App\Components\Reporting\Interfaces;

use App\Components\Reporting\Models\Filters\FinancialReportFilterData;

/**
 * Interface FinancialReportServiceInterface
 *
 * @package App\Components\Reporting\Interfaces
 */
interface FinancialReportServiceInterface
{
    /**
     * Returns financial report data.
     *
     * @param \App\Components\Reporting\Models\Filters\FinancialReportFilterData $filter Options to filter.
     *
     * @return array
     *
     * @throws \JsonMapper_Exception
     */
    public function getReport(FinancialReportFilterData $filter): array;
}
