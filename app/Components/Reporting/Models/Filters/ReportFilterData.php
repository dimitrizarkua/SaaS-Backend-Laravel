<?php

namespace App\Components\Reporting\Models\Filters;

use App\Core\JsonModel;
use Illuminate\Support\Carbon;

/**
 * Class ReportFilterData
 *
 * @package App\Models
 */
class ReportFilterData extends JsonModel
{
    /**
     * @var int
     */
    public $location_id;

    /**
     * @var array|null Tags identifiers.
     */
    public $tag_ids = [];

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    private $current_date_from;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    private $current_date_to;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    private $previous_date_from;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    private $previous_date_to;

    /**
     * @param string $dateFrom
     *
     * @return ReportFilterData
     */
    public function setCurrentDateFrom(string $dateFrom): self
    {
        $this->current_date_from = new Carbon($dateFrom);

        return $this;
    }

    /**
     * @param string $dateTo
     *
     * @return ReportFilterData
     */
    public function setCurrentDateTo(string $dateTo): self
    {
        $this->current_date_to = new Carbon($dateTo);

        return $this;
    }

    /**
     * @param string $dateFrom
     *
     * @return ReportFilterData
     */
    public function setPreviousDateFrom(string $dateFrom): self
    {
        $this->previous_date_from = new Carbon($dateFrom);

        return $this;
    }

    /**
     * @param string $dateTo
     *
     * @return ReportFilterData
     */
    public function setPreviousDateTo(string $dateTo): self
    {
        $this->previous_date_to = new Carbon($dateTo);

        return $this;
    }

    /**
     * @return \Illuminate\Support\Carbon
     */
    public function getCurrentDateFrom(): \Illuminate\Support\Carbon
    {
        if (null === $this->current_date_from) {
            return now()->startOfMonth();
        }

        return $this->current_date_from;
    }

    /**
     * @return \Illuminate\Support\Carbon
     */
    public function getCurrentDateTo(): \Illuminate\Support\Carbon
    {
        if (null === $this->current_date_to) {
            return now()->endOfMonth();
        }

        return $this->current_date_to;
    }

    /**
     * @return \Illuminate\Support\Carbon
     */
    public function getPreviousDateFrom(): \Illuminate\Support\Carbon
    {
        if (null === $this->previous_date_from) {
            return now()->startOfMonth();
        }

        return $this->previous_date_from;
    }

    /**
     * @return \Illuminate\Support\Carbon
     */
    public function getPreviousDateTo(): \Illuminate\Support\Carbon
    {
        if (null === $this->previous_date_to) {
            return now()->endOfMonth();
        }

        return $this->previous_date_to;
    }

    /**
     * @param int|null $locationId
     *
     * @return ReportFilterData
     */
    public function setLocationId(?int $locationId): self
    {
        if (null !== $locationId) {
            $this->location_id = $locationId;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getLocationId(): int
    {
        return $this->location_id;
    }

    /**
     * @param array $tagIds
     *
     * @return ReportFilterData
     */
    public function setTagIds(array $tagIds): self
    {
        $this->tag_ids = $tagIds;

        return $this;
    }

    /**
     * Tags identifiers.
     *
     * @return array
     */
    public function getTagIds(): array
    {
        return $this->tag_ids;
    }
}
