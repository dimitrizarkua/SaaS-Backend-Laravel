<?php

namespace App\Components\Reporting\Models\Filters;

use App\Core\JsonModel;
use Illuminate\Support\Carbon;

/**
 * Class ContactVolumeReportFilter
 *
 * @package App\Components\Reporting\Models\Filters
 */
class ContactVolumeReportFilter extends JsonModel
{
    /**
     * @var int|null
     */
    public $staff_id;

    /**
     * @var int
     */
    public $location_id;

    /**
     * @var int
     */
    public $contact_id;

    /**
     * @var \Illuminate\Support\Carbon
     */
    public $date_from;

    /**
     * @var \Illuminate\Support\Carbon
     */
    public $date_to;

    /**
     * @var array|null
     */
    public $tag_ids = [];

    /**
     * @return \Illuminate\Support\Carbon
     */
    public function getDateFrom(): Carbon
    {
        return $this->date_from;
    }

    /**
     * @param string $dateFrom
     *
     * @return self
     */
    public function setDateFrom(string $dateFrom): self
    {
        $this->date_from = new Carbon($dateFrom);

        return $this;
    }

    /**
     * @return \Illuminate\Support\Carbon
     */
    public function getDateTo(): Carbon
    {
        return $this->date_to;
    }

    /**
     * @param string $dateTo
     *
     * @return self
     */
    public function setDateTo(string $dateTo): self
    {
        $this->date_to = new Carbon($dateTo);

        return $this;
    }
}
