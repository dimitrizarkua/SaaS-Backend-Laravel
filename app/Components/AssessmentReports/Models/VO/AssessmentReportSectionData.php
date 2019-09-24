<?php

namespace App\Components\AssessmentReports\Models\VO;

/**
 * Class AssessmentReportSectionData
 *
 * @package App\Components\AssessmentReports\Models\VO
 */
class AssessmentReportSectionData extends AbstractAssessmentReportData
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var integer
     */
    public $position;

    /**
     * @var string|null
     */
    public $heading;

    /**
     * @var string|null
     */
    public $heading_style;

    /**
     * @var int|null
     */
    public $heading_color;

    /**
     * @var string|null
     */
    public $text;
}
