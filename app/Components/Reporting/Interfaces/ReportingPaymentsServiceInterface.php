<?php

namespace App\Components\Reporting\Interfaces;

use App\Components\Reporting\Models\VO\InvoicePaymentsReportFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Interface ReportingPaymentsServiceInterface
 *
 * @package App\Components\Reporting\Interfaces
 */
interface ReportingPaymentsServiceInterface
{
    /**
     * Returns builder for invoices payments report.
     *
     * @param  InvoicePaymentsReportFilter $filter Filter instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getInvoicePaymentsReportBuilder(InvoicePaymentsReportFilter $filter): Builder;
}
