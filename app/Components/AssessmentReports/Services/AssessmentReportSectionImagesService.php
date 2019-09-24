<?php

namespace App\Components\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Models\AssessmentReportSectionImage;

/**
 * Class AssessmentReportSectionImagesService
 *
 * @package App\Components\AssessmentReports\Services
 */
class AssessmentReportSectionImagesService extends AssessmentReportSectionEntityService
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return AssessmentReportSectionImage::class;
    }

    /**
     * {@inheritdoc}
     */
    public function matchTypes(string $type): bool
    {
        return AssessmentReportSectionTypes::IMAGE === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityRelation(): string
    {
        return 'image';
    }
}
