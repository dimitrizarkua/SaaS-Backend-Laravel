<?php

namespace App\Components\AssessmentReports\Models\VO;

/**
 * Class AssessmentReportData
 *
 * @package App\Components\AssessmentReports\Models\VO
 */
class AssessmentReportData extends AbstractAssessmentReportData
{
    /**
     * @var string|null
     */
    public $heading;

    /**
     * @var string|null
     */
    public $subheading;

    /**
     * @var string
     */
    public $date;
}
