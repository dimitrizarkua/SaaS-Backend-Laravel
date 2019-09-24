<?php

namespace App\Components\AssessmentReports\Models\VO;

use App\Core\JsonModel;

/**
 * Class AbstractAssessmentReportData
 *
 * @package App\Components\AssessmentReports\Models\VO
 */
abstract class AbstractAssessmentReportData extends JsonModel
{
    /**
     * AbstractAssessmentReportData constructor.
     *
     * @param array $properties Optional properties to be set to current instance.
     *
     * @throws \JsonMapper_Exception
     */
    public function __construct(array $properties = [])
    {
        $hidden       = array_diff_key(get_class_vars(static::class), $properties);
        $this->hidden = array_merge(array_keys($hidden), $this->hidden);
        parent::__construct($properties);
    }
}
