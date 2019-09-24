<?php

namespace App\Components\Reporting\Models\Filters;

use App\Core\JsonModel;

/**
 * Class ReportFilter
 *
 * @package App\Models
 */
class ReportFilter extends JsonModel
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
     * @var \Illuminate\Support\Carbon
     */
    public $date_from;

    /**
     * @var \Illuminate\Support\Carbon
     */
    public $date_to;
}
