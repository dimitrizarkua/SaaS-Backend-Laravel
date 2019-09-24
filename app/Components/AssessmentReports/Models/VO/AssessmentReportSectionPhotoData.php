<?php

namespace App\Components\AssessmentReports\Models\VO;

/**
 * Class AssessmentReportSectionPhotoData
 *
 * @package App\Components\AssessmentReports\Models\VO
 */
class AssessmentReportSectionPhotoData extends AbstractAssessmentReportData
{
    /**
     * @var int|null
     */
    public $photo_id;

    /**
     * @var int
     */
    public $position;

    /**
     * @var string|null
     */
    public $caption;
}
