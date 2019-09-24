<?php

namespace App\Components\AssessmentReports\Models\VO;

/**
 * Class AssessmentReportCostItemData
 *
 * @package App\Components\AssessmentReports\Models\VO
 */
class AssessmentReportCostItemData extends AbstractAssessmentReportData
{
    /**
     * @var int
     */
    public $assessment_report_costing_stage_id;

    /**
     * @var integer
     */
    public $gs_code_id;

    /**
     * @var int
     */
    public $position;

    /**
     * @var string
     */
    public $description;

    /**
     * @var integer
     */
    public $quantity;

    /**
     * @var float
     */
    public $unit_cost;

    /**
     * @var float
     */
    public $discount;

    /**
     * @var float
     */
    public $markup;

    /**
     * @var integer
     */
    public $tax_rate_id;
}
