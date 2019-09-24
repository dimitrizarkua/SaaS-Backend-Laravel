<?php

namespace App\Components\AssessmentReports\Models\VO;

/**
 * Class AssessmentReportSectionImageData
 *
 * @package App\Components\AssessmentReports\Models\VO
 */
class AssessmentReportSectionImageData extends AbstractAssessmentReportData
{
    /**
     * @var int|null
     */
    public $photo_id;

    /**
     * @var string|null
     */
    public $caption;

    /**
     * @var int
     */
    public $desired_width;
}
