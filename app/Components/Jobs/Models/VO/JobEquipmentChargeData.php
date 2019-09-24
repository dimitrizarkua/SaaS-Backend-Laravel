<?php

namespace App\Components\Jobs\Models\VO;

use App\Components\Jobs\Models\JobEquipmentChargingInterval;
use App\Core\JsonModel;

/**
 * Class JobEquipmentChargeData
 *
 * @package App\Components\Jobs\Models\VO
 */
class JobEquipmentChargeData extends JsonModel
{
    /** @var float */
    public $total_amount = 0;

    /** @var int */
    public $intervals_count = 0;

    /** @var JobEquipmentChargingInterval */
    public $interval;

    /**
     * @param float $totalAmount
     *
     * @return self
     */
    public function incrementTotalAmount(float $totalAmount): self
    {
        $this->total_amount += $totalAmount;

        return $this;
    }

    /**
     * @param int $intervalsCount
     *
     * @return self
     */
    public function incrementIntervalsCount(int $intervalsCount): self
    {
        $this->intervals_count += $intervalsCount;

        return $this;
    }

    /**
     * @param JobEquipmentChargingInterval $interval
     *
     * @return self
     */
    public function setInterval(JobEquipmentChargingInterval $interval): self
    {
        $this->interval = $interval;

        return $this;
    }
}
