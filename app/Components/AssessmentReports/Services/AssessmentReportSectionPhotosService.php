<?php

namespace App\Components\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Models\AssessmentReportSectionPhoto;

/**
 * Class AssessmentReportSectionPhotosService
 *
 * @package App\Components\AssessmentReports\Services
 */
class AssessmentReportSectionPhotosService extends AssessmentReportSectionEntityService
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return AssessmentReportSectionPhoto::class;
    }

    /**
     * {@inheritdoc}
     */
    public function matchTypes(string $type): bool
    {
        return AssessmentReportSectionTypes::PHOTOS === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityRelation(): string
    {
        return 'photos';
    }
}
