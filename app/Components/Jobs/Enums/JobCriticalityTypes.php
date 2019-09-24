<?php

namespace App\Components\Jobs\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class JobCriticalityTypes
 *
 * @package App\Components\Jobs\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Job criticality type",
 *     enum={"Critical","Non-Critical","Semi-Critical"},
 * )
 */
class JobCriticalityTypes extends Enum
{
    public const CRITICAL      = 'Critical';
    public const NON_CRITICAL  = 'Non-Critical';
    public const SEMI_CRITICAL = 'Semi-Critical';

    protected static $values = [
        'CRITICAL'      => self::CRITICAL,
        'NON_CRITICAL'  => self::NON_CRITICAL,
        'SEMI_CRITICAL' => self::SEMI_CRITICAL,
    ];
}
