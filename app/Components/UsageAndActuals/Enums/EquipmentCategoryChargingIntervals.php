<?php

namespace App\Components\UsageAndActuals\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class EquipmentCategoryChargingIntervals
 *
 * @package App\Components\UsageAndActuals\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Charging interval",
 *     enum={"each", "hour", "day", "week"},
 * )
 */
class EquipmentCategoryChargingIntervals extends Enum
{
    public const DEFAULT_DAY_TO_WEEK_RATE_IN_DAYS = 4;

    public const EACH = 'each';
    public const HOUR = 'hour';
    public const DAY  = 'day';
    public const WEEK = 'week';

    protected static $values = [
        'EACH' => self::EACH,
        'HOUR' => self::HOUR,
        'DAY'  => self::DAY,
        'WEEK' => self::WEEK,
    ];
}
