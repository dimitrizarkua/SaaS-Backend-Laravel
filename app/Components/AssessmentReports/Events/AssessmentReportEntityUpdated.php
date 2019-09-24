<?php

namespace App\Components\AssessmentReports\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class AssessmentReportEntityUpdated
 *
 * @package App\Components\AssessmentReports\Events
 */
class AssessmentReportEntityUpdated
{
    use SerializesModels;

    /** @var int */
    public $assessmentReportId;

    /**
     * Create a new AssessmentReportEntityUpdated instance.
     *
     * @param int $assessmentReportId Identifier of created/updated/deleted assessment report entity.
     */
    public function __construct(int $assessmentReportId)
    {
        $this->assessmentReportId = $assessmentReportId;
    }
}
