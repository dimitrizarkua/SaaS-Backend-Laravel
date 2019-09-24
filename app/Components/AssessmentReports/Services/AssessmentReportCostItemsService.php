<?php

namespace App\Components\AssessmentReports\Services;

use App\Components\AssessmentReports\Models\AssessmentReportCostItem;

/**
 * Class AssessmentReportCostItemsService
 *
 * @package App\Components\AssessmentReports\Services
 */
class AssessmentReportCostItemsService extends AssessmentReportEntityService
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return AssessmentReportCostItem::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityRelation(): string
    {
        return 'costItems';
    }
}
