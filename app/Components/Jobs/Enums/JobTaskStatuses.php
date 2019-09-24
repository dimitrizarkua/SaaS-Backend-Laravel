<?php

namespace App\Components\Jobs\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class JobTaskStatuses
 *
 * @OA\Schema(
 *     type="string",
 *     example="active",
 *     enum={"Active","Completed"}
 * )
 *
 * @package App\Components\Jobs\Enums
 */
class JobTaskStatuses extends Enum
{
    public const ACTIVE    = 'Active';
    public const COMPLETED = 'Completed';

    protected static $values = [
        'ACTIVE'    => self::ACTIVE,
        'COMPLETED' => self::COMPLETED,
    ];
}
