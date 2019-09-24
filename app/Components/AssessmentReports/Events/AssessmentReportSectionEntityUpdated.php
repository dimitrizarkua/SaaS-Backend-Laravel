<?php

namespace App\Components\AssessmentReports\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class AssessmentReportSectionEntityUpdated
 *
 * @package App\Components\AssessmentReports\Events
 */
class AssessmentReportSectionEntityUpdated
{
    use SerializesModels;

    /** @var int */
    public $assessmentReportId;

    /**
     * Create a new AssessmentReportSectionEntityUpdated instance.
     *
     * @param int $assessmentReportId Identifier of created/updated/deleted assessment report section entity.
     */
    public function __construct(int $assessmentReportId)
    {
        $this->assessmentReportId = $assessmentReportId;
    }
}
