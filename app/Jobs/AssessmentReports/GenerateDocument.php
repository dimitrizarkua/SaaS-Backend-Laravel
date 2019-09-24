<?php

namespace App\Jobs\AssessmentReports;

use App\Components\AssessmentReports\Interfaces\AssessmentReportsServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class GenerateDocument
 *
 * @package App\Jobs\AssessmentReports
 */
class GenerateDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int */
    private $assessmentReportId;

    /** @var AssessmentReportsServiceInterface */
    private $service;

    /**
     * GenerateDocument constructor.
     *
     * @param AssessmentReportsServiceInterface $service            Assessment Reports Service instance.
     * @param int                               $assessmentReportId Identifier of AR for which PDF should be created.
     */
    public function __construct(AssessmentReportsServiceInterface $service, int $assessmentReportId)
    {
        $this->assessmentReportId = $assessmentReportId;
        $this->service            = $service;
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        $this->service->generateDocument($this->assessmentReportId);
    }
}
