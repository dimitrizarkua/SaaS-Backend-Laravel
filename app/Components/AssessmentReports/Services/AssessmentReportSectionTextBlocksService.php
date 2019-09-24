<?php

namespace App\Components\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Models\AssessmentReportSectionTextBlock;

/**
 * Class AssessmentReportSectionTextBlocksService
 *
 * @package App\Components\AssessmentReports\Services
 */
class AssessmentReportSectionTextBlocksService extends AssessmentReportSectionEntityService
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return AssessmentReportSectionTextBlock::class;
    }

    /**
     * {@inheritdoc}
     */
    public function matchTypes(string $type): bool
    {
        return in_array($type, AssessmentReportSectionTypes::$textSectionTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityRelation(): string
    {
        return 'textBlocks';
    }
}
