<?php

namespace App\Components\Reporting\Interfaces;

use App\Components\Reporting\Models\Filters\IncomeReportFilter;

/**
 * Interface IncomeReportServiceInterface
 * Finance: Report - Income by Account Summary.
 *
 * @package App\Components\Reporting\Interfaces
 */
interface IncomeReportServiceInterface
{
    /**
     * Returns data for income summary report based on cash basis. Cash bash basis means that invoices should be
     * paid in full and amount should be calculated without tax.
     *
     * @param IncomeReportFilter $filter Filter instance.
     *
     * @return array Report data
     */
    public function getIncomeReportData(IncomeReportFilter $filter): array;
}
