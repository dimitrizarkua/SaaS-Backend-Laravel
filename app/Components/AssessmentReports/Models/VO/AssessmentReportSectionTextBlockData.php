<?php

namespace App\Components\AssessmentReports\Models\VO;

/**
 * Class AssessmentReportSectionTextBlockData
 *
 * @package App\Components\AssessmentReports\Models\VO
 */
class AssessmentReportSectionTextBlockData extends AbstractAssessmentReportData
{
    /**
     * @var int
     */
    public $position;

    /**
     * @var string|null
     */
    public $text;
}
