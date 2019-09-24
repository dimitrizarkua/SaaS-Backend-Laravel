<?php

namespace App\Components\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Models\AssessmentReportSectionCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportSectionEntity;

/**
 * Class AssessmentReportSectionCostItemsService
 *
 * @package App\Components\AssessmentReports\Services
 */
class AssessmentReportSectionCostItemsService extends AssessmentReportSectionEntityService
{
    /**
     * {@inheritdoc}
     */
    public function getEntity(int $sectionId, int $entityId): AssessmentReportSectionEntity
    {
        return AssessmentReportSectionCostItem::query()
            ->where('assessment_report_section_id', $sectionId)
            ->where('assessment_report_cost_item_id', $entityId)
            ->firstOrFail();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return AssessmentReportSectionCostItem::class;
    }

    /**
     * {@inheritdoc}
     */
    public function matchTypes(string $type): bool
    {
        return AssessmentReportSectionTypes::COSTS === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityRelation(): string
    {
        return 'costItems';
    }
}
