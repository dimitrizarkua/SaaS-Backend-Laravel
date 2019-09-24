<?php

namespace App\Components\Reporting;

use App\Components\Reporting\Interfaces\CostingSummaryInterface;
use App\Components\Reporting\Interfaces\ReportingGLAccountServiceInterface;
use App\Components\Reporting\Interfaces\IncomeReportServiceInterface;
use App\Components\Reporting\Interfaces\ReportingPaymentsServiceInterface;
use App\Components\Reporting\Services\ContactVolumeReportService;
use App\Components\Reporting\Interfaces\ContactVolumeReportServiceInterface;
use App\Components\Reporting\Services\CostingSummaryService;
use App\Components\Reporting\Services\ReportingGLAccountService;
use App\Components\Reporting\Services\IncomeReportService;
use App\Components\Reporting\Services\ReportingPaymentsService;
use Illuminate\Support\ServiceProvider;

/**
 * Class ReportingServiceProvider
 *
 * @package App\Components\Reporting
 */
class ReportingServiceProvider extends ServiceProvider
{
    public $bindings = [
        ReportingPaymentsServiceInterface::class   => ReportingPaymentsService::class,
        IncomeReportServiceInterface::class        => IncomeReportService::class,
        ReportingGLAccountServiceInterface::class  => ReportingGLAccountService::class,
        CostingSummaryInterface::class             => CostingSummaryService::class,
        ContactVolumeReportServiceInterface::class => ContactVolumeReportService::class,
    ];
}
