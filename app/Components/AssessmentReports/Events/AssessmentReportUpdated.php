<?php

namespace App\Components\AssessmentReports\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class AssessmentReportUpdated
 *
 * @package App\Components\AssessmentReports\Events
 */
class AssessmentReportUpdated
{
    use SerializesModels;

    /** @var int */
    public $assessmentReportId;

    /**
     * Create a new AssessmentReportUpdated instance.
     *
     * @param int $assessmentReportId Identifier of updated assessment report.
     */
    public function __construct(int $assessmentReportId)
    {
        $this->assessmentReportId = $assessmentReportId;
    }
}
