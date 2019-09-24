<?php

namespace App\Components\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Models\AssessmentReportSectionRoom;

/**
 * Class AssessmentReportSectionRoomsService
 *
 * @package App\Components\AssessmentReports\Services
 */
class AssessmentReportSectionRoomsService extends AssessmentReportSectionEntityService
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return AssessmentReportSectionRoom::class;
    }

    /**
     * {@inheritdoc}
     */
    public function matchTypes(string $type): bool
    {
        return AssessmentReportSectionTypes::ROOM === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityRelation(): string
    {
        return 'room';
    }
}
