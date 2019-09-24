<?php

namespace App\Components\Jobs\Models\VO;

use App\Core\JsonModel;
use Illuminate\Support\Carbon;

/**
 * Class CreateJobEquipmentData
 *
 * @package App\Components\Jobs\Models\VO
 */
class CreateJobEquipmentData extends JsonModel
{
    /** @var int */
    public $equipment_id;

    /** @var Carbon */
    public $started_at;

    /** @var Carbon|null */
    public $ended_at;

    /**
     * JobEquipmentData constructor.
     *
     * @param array|null $properties Optional properties to be set to current instance.
     *
     * @throws \JsonMapper_Exception
     */
    public function __construct(?array $properties = null)
    {
        $hidden       = array_diff_key(get_class_vars(self::class), $properties);
        $this->hidden = array_merge(array_keys($hidden), $this->hidden);
        parent::__construct($properties);
    }

    /**
     * @param string $startedAt
     *
     * @return self
     */
    public function setStartedAt(string $startedAt): self
    {
        $this->started_at = new Carbon($startedAt);

        return $this;
    }

    /**
     * @param string|null $endedAt
     *
     * @return self
     */
    public function setEndedAt(?string $endedAt): self
    {
        if (null !== $endedAt) {
            $this->ended_at = new Carbon($endedAt);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getEquipmentId(): ?int
    {
        return $this->equipment_id;
    }

    /**
     * @return Carbon
     */
    public function getStartedAt(): ?Carbon
    {
        return $this->started_at;
    }

    /**
     * @return Carbon|null
     */
    public function getEndedAt(): ?Carbon
    {
        return $this->ended_at;
    }
}
