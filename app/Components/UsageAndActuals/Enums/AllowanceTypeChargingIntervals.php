<?php

namespace App\Components\UsageAndActuals\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class AllowanceTypeChargingIntervals
 *
 * @package App\Components\UsageAndActuals\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Charging interval",
 *     enum={"Each","Day","Hour","Week","KM (Kilometre)","SQ metre (Square metre)","M3 (Cubic metre)"},
 * )
 */
class AllowanceTypeChargingIntervals extends Enum
{
    public const EACH     = 'Each';
    public const DAY      = 'Day';
    public const HOUR     = 'Hour';
    public const WEEK     = 'Week';
    public const KM       = 'KM (Kilometre)';
    public const SQ_METRE = 'SQ metre (Square metre)';
    public const M3       = 'M3 (Cubic metre)';

    protected static $values = [
        'EACH'     => self::EACH,
        'DAY'      => self::DAY,
        'HOUR'     => self::HOUR,
        'WEEK'     => self::WEEK,
        'KM'       => self::KM,
        'SQ_METRE' => self::SQ_METRE,
        'M3'       => self::M3,
    ];
}
