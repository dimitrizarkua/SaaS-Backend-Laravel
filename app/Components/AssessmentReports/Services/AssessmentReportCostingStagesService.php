<?php

namespace App\Components\AssessmentReports\Services;

use App\Components\AssessmentReports\Models\AssessmentReportCostingStage;

/**
 * Class AssessmentReportCostingStagesService
 *
 * @package App\Components\AssessmentReports\Services
 */
class AssessmentReportCostingStagesService extends AssessmentReportEntityService
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return AssessmentReportCostingStage::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityRelation(): string
    {
        return 'costingStages';
    }
}
