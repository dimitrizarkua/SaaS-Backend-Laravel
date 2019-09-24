<?php

namespace App\Components\AssessmentReports\Listeners;

use App\Components\AssessmentReports\Events\AssessmentReportCreated;
use App\Components\AssessmentReports\Events\AssessmentReportEntityUpdated;
use App\Components\AssessmentReports\Events\AssessmentReportSectionEntityUpdated;
use App\Components\AssessmentReports\Events\AssessmentReportUpdated;
use App\Components\AssessmentReports\Services\AssessmentReportsService;
use App\Jobs\AssessmentReports\GenerateDocument;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\App;

/**
 * Class AssessmentReportEventsListener
 *
 * @package Components\AssessmentReports\Listeners
 */
class AssessmentReportEventsListener
{
    /**
     * @var array
     */
    protected $events = [
        AssessmentReportCreated::class              => '@onAssessmentReportTouched',
        AssessmentReportUpdated::class              => '@onAssessmentReportTouched',
        AssessmentReportEntityUpdated::class        => '@onAssessmentReportTouched',
        AssessmentReportSectionEntityUpdated::class => '@onAssessmentReportTouched',
    ];

    /**
     * @param Dispatcher $dispatcher
     */
    public function subscribe(Dispatcher $dispatcher)
    {
        if (App::environment('testing')) {
            return;
        }

        foreach ($this->events as $eventClassName => $method) {
            $dispatcher->listen($eventClassName, self::class . $method);
        }
    }

    /**
     * @param $event
     */
    public function onAssessmentReportTouched($event): void
    {
        $service = app()->make(AssessmentReportsService::class);
        GenerateDocument::dispatch($service, $event->assessmentReportId);
    }
}
