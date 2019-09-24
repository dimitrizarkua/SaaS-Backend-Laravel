<?php

namespace App\Components\AssessmentReports\Interfaces;

use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\Jobs\Interfaces\StatusWorkflowInterface;

/**
 * Interface AssessmentReportStatusWorkflowInterface
 *
 * @package App\Components\AssessmentReports\Interfaces
 */
interface AssessmentReportStatusWorkflowInterface extends StatusWorkflowInterface
{
    /**
     * Set current assessment report.
     *
     * @param AssessmentReport $assessmentReport
     *
     * @return self
     */
    public function setAssessmentReport(AssessmentReport $assessmentReport): self;
}
