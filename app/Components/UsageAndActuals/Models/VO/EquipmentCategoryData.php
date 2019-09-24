<?php

namespace App\Components\UsageAndActuals\Models\VO;

use App\Core\JsonModel;

/**
 * Class EquipmentCategoryData
 *
 * @package App\Components\UsageAndActuals\Models\VO
 */
class EquipmentCategoryData extends JsonModel
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $is_airmover;

    /**
     * @var bool
     */
    public $is_dehum;

    /**
     * @var float
     */
    public $default_buy_cost_per_interval;

    /**
     * @var float
     */
    public $charging_rate_per_interval;

    /**
     * @var string
     */
    public $charging_interval;

    /**
     * @var int
     */
    public $max_count_to_the_next_interval = 0;

    /**
     * @var float
     */
    public $charge_rate_per_week;

    /**
     * @return string
     */
    public function getChargingInterval(): string
    {
        return $this->charging_interval;
    }

    /**
     * @return float
     */
    public function getChargingRatePerInterval(): float
    {
        return $this->charging_rate_per_interval;
    }

    /**
     * @return int
     */
    public function getMaxCountToTheNextInterval(): int
    {
        return $this->max_count_to_the_next_interval;
    }

    /**
     * @return float|null
     */
    public function getChargeRatePerWeek(): ?float
    {
        return $this->charge_rate_per_week;
    }
}
