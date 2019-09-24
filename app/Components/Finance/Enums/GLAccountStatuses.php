<?php

namespace App\Components\Finance\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class GLAccountStatuses
 *
 * @package App\Components\Finance\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="GL account statuses",
 *     enum={"active"},
 * )
 */
class GLAccountStatuses extends Enum
{
    public const ACTIVE = 'active';

    protected static $values = [
        'ACTIVE' => self::ACTIVE,
    ];
}
