<?php

namespace App\Components\Reporting\Models\VO;

use App\Core\JsonModel;
use Illuminate\Support\Carbon;

/**
 * Class GLAccountTrialReportFilterData
 *
 * @package App\Components\Reporting\Models\VO
 */
class GLAccountTrialReportFilterData extends JsonModel
{
    /**
     * @var int
     */
    private $location_id;

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var \Illuminate\Support\Carbon
     */
    private $date_to;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    private $date_from;

    /**
     * @param mixed $dateTo
     *
     * @throws \Throwable
     * @return self
     */
    public function setDateTo($dateTo): self
    {
        if ($dateTo instanceof Carbon) {
            $this->date_to = $dateTo;
        } else {
            $this->date_to = Carbon::make($dateTo);
        }
        if (null === $this->date_to) {
            $this->date_to = now();
        }

        return $this;
    }

    /**
     * @return \Illuminate\Support\Carbon
     */
    public function getDateTo(): \Illuminate\Support\Carbon
    {
        return $this->date_to;
    }

    /**
     * @param mixed $dateFrom
     *
     * @throws \Throwable
     * @return self
     */
    public function setDateFrom($dateFrom): self
    {
        if ($dateFrom instanceof Carbon) {
            $this->date_from = $dateFrom;
        } else {
            $this->date_from = Carbon::make($dateFrom);
        }

        return $this;
    }

    /**
     * @return \Illuminate\Support\Carbon|null
     */
    public function getDateFrom(): ?\Illuminate\Support\Carbon
    {
        return $this->date_from;
    }

    /**
     * @return int|null
     **/
    public function getLocationId(): ?int
    {
        return $this->location_id;
    }

    /**
     * @param int|null $locationId
     *
     * @return self
     */
    public function setLocationId(?int $locationId): self
    {
        $this->location_id = $locationId;

        return $this;
    }
}
