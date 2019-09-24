<?php

namespace App\Components\Jobs\Models\VO;

use App\Core\JsonModel;

/**
 * Class JobRoomData
 *
 * @package App\Components\Jobs\Models\VO
 */
class JobRoomData extends JsonModel
{
    /** @var int|null */
    public $flooring_type_id;

    /** @var string */
    public $name;

    /** @var float|null */
    public $total_sqm;

    /** @var float|null */
    public $affected_sqm;

    /** @var float|null */
    public $non_restorable_sqm;

    /**
     * JobRoomData constructor.
     *
     * @param array|null $properties Optional properties to be set to current instance.
     *
     * @throws \JsonMapper_Exception
     */
    public function __construct(?array $properties = null)
    {
        $hidden       = array_diff_key(get_class_vars(static::class), $properties);
        $this->hidden = array_merge(array_keys($hidden), $this->hidden);
        parent::__construct($properties);
    }
}
