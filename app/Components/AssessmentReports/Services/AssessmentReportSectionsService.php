<?php

namespace App\Components\AssessmentReports\Services;

use App\Components\AssessmentReports\Models\AssessmentReportSection;

/**
 * Class AssessmentReportSectionsService
 *
 * @package App\Components\AssessmentReports\Services
 */
class AssessmentReportSectionsService extends AssessmentReportEntityService
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return AssessmentReportSection::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityRelation(): string
    {
        return 'sections';
    }
}
