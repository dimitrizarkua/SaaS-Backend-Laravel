<?php

namespace App\Components\AssessmentReports\Models\VO;

use App\Core\JsonModel;

/**
 * Class AssessmentReportSectionCostsCostSummaryData
 *
 * @package App\Components\AssessmentReports\Models\VO
 */
class AssessmentReportSectionCostsCostSummaryData extends JsonModel
{
    /**
     * @var float
     */
    public $sub_total = 0;

    /**
     * @var float
     */
    public $gst = 0;

    /**
     * @var float
     */
    public $total_cost = 0;

    /**
     * @param float $subTotal
     *
     * @return self
     */
    public function incrementSubTotal(float $subTotal): self
    {
        $this->sub_total += $subTotal;

        return $this;
    }

    /**
     * @param float $gst
     *
     * @return self
     */
    public function incrementGST(float $gst): self
    {
        $this->gst += $gst;

        return $this;
    }

    /**
     * @return self
     */
    public function calculateCostSummary(): self
    {
        $this->sub_total  = round($this->sub_total, 2);
        $this->gst        = round($this->gst, 2);
        $this->total_cost = round($this->sub_total + $this->gst, 2);

        return $this;
    }
}
