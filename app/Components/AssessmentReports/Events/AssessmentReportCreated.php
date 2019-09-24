<?php

namespace App\Components\AssessmentReports\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class AssessmentReportCreated
 *
 * @package App\Components\AssessmentReports\Events
 */
class AssessmentReportCreated
{
    use SerializesModels;

    /** @var int */
    public $assessmentReportId;

    /**
     * Create a new AssessmentReportCreated instance.
     *
     * @param int $assessmentReportId Identifier of created assessment report.
     */
    public function __construct(int $assessmentReportId)
    {
        $this->assessmentReportId = $assessmentReportId;
    }
}
