<?php

namespace App\Components\AssessmentReports\Interfaces;

use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\VO\AssessmentReportData;

/**
 * Interface AssessmentReportsServiceInterface
 *
 * @package App\Components\AssessmentReports\Interfaces
 */
interface AssessmentReportsServiceInterface
{
    /**
     * Returns AR.
     *
     * @param int $assessmentReportId AR identifier.
     *
     * @return AssessmentReport
     */
    public function getAssessmentReport(int $assessmentReportId): AssessmentReport;

    /**
     * Returns full representation of AR.
     *
     * @param int $assessmentReportId AR identifier.
     *
     * @return AssessmentReport
     */
    public function getFullAssessmentReport(int $assessmentReportId): AssessmentReport;

    /**
     * Creates AR.
     *
     * @param AssessmentReportData $data   Data for create.
     * @param int                  $jobId  Job identifier.
     * @param int                  $userId Identifier of user who creating AR.
     *
     * @return AssessmentReport
     */
    public function createAssessmentReport(AssessmentReportData $data, int $jobId, int $userId): AssessmentReport;

    /**
     * Updates AR.
     *
     * @param AssessmentReportData $data               Data for update.
     * @param int                  $assessmentReportId AR identifier.
     *
     * @return AssessmentReport
     */
    public function updateAssessmentReport(AssessmentReportData $data, int $assessmentReportId): AssessmentReport;

    /**
     * Removes AR.
     *
     * @param int $assessmentReportId AR identifier.
     *
     * @return void
     */
    public function deleteAssessmentReport(int $assessmentReportId): void;

    /**
     * Checks whether assessment report is approved and throws exception.
     *
     * @param int $assessmentReportId AR identifier.
     *
     * @return void
     */
    public function throwExceptionIfAssessmentReportIsApprovedOrCancelled(int $assessmentReportId): void;

    /**
     * Generate print version of the assessment report. Print version will be saved as document.
     *
     * @param int $assessmentReportId AR identifier.
     *
     * @return int
     */
    public function generateDocument(int $assessmentReportId): int;

    /**
     * Returns latest status and total amount of specified assessment report.
     *
     * @param int $assessmentReportId AR identifier.
     *
     * @return array
     */
    public function getStatusAndTotal(int $assessmentReportId): array;
}
