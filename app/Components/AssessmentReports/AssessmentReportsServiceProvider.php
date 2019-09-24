<?php

namespace App\Components\AssessmentReports;

use App\Components\AssessmentReports\Interfaces\AssessmentReportsServiceInterface;
use App\Components\AssessmentReports\Interfaces\AssessmentReportStatusWorkflowInterface;
use App\Components\AssessmentReports\Models\AssessmentReportCostingStage;
use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportSection;
use App\Components\AssessmentReports\Models\AssessmentReportSectionCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportSectionPhoto;
use App\Components\AssessmentReports\Models\AssessmentReportSectionTextBlock;
use App\Components\AssessmentReports\Services\AssessmentReportsService;
use App\Components\AssessmentReports\Services\AssessmentReportStatusWorkflow;
use App\Observers\PositionableObserver;
use Illuminate\Support\ServiceProvider;

/**
 * Class AssessmentReportsServiceProvider
 *
 * @package App\Components\AssessmentReports
 */
class AssessmentReportsServiceProvider extends ServiceProvider
{
    public $bindings = [
        AssessmentReportsServiceInterface::class       => AssessmentReportsService::class,
        AssessmentReportStatusWorkflowInterface::class => AssessmentReportStatusWorkflow::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        AssessmentReportSection::observe(PositionableObserver::class);
        AssessmentReportCostingStage::observe(PositionableObserver::class);
        AssessmentReportCostItem::observe(PositionableObserver::class);
        AssessmentReportSectionTextBlock::observe(PositionableObserver::class);
        AssessmentReportSectionPhoto::observe(PositionableObserver::class);
        AssessmentReportSectionCostItem::observe(PositionableObserver::class);
    }
}
