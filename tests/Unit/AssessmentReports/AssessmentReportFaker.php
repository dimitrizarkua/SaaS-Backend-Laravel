<?php

namespace Tests\Unit\AssessmentReports;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostingStage;
use App\Components\AssessmentReports\Models\AssessmentReportSection;
use App\Components\AssessmentReports\Models\AssessmentReportStatus;

/**
 * Trait AssessmentReportFaker
 *
 * @package Tests\Unit\AssessmentReports
 * @property \Faker\Generator faker
 */
trait AssessmentReportFaker
{
    /**
     * @param string|null $status
     * @param array       $attributes
     *
     * @return AssessmentReport
     */
    protected function fakeAssessmentReportWithStatus(string $status = null, array $attributes = []): AssessmentReport
    {
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create($attributes);

        if (null === $status) {
            $status = AssessmentReportStatuses::DRAFT;
        }
        factory(AssessmentReportStatus::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'status'               => $status,
        ]);

        return $assessmentReport->fresh();
    }

    /**
     * @param string|null $type
     * @param string|null $status
     * @param array       $reportAttrs
     *
     * @return AssessmentReportSection
     */
    protected function fakeAssessmentReportSection(
        string $type = null,
        string $status = null,
        array $reportAttrs = []
    ): AssessmentReportSection {
        $assessmentReport = $this->fakeAssessmentReportWithStatus($status, $reportAttrs);

        if (null === $type) {
            $type = $this->faker->randomElement(AssessmentReportSectionTypes::values());
        }

        /** @var AssessmentReportSection $assessmentReportSection */
        $assessmentReportSection = factory(AssessmentReportSection::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'type'                 => $type,
        ]);

        return $assessmentReportSection;
    }

    /**
     * @param string|null $status
     * @param array       $reportAttrs
     *
     * @return AssessmentReportCostingStage
     */
    protected function fakeAssessmentReportCostingStage(
        string $status = null,
        array $reportAttrs = []
    ): AssessmentReportCostingStage {
        $assessmentReport = $this->fakeAssessmentReportWithStatus($status, $reportAttrs);

        /** @var AssessmentReportCostingStage $assessmentReportCostingStage */
        $assessmentReportCostingStage = factory(AssessmentReportCostingStage::class)->create([
            'assessment_report_id' => $assessmentReport->id,
        ]);

        return $assessmentReportCostingStage;
    }
}
