<?php

namespace App\Components\Jobs\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class ClaimTypes
 *
 * @package App\Components\Jobs\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Claim type",
 *     enum={"Contents Only","Structure Only","Contents and Structure"},
 * )
 */
class ClaimTypes extends Enum
{
    public const CONTENTS_ONLY          = 'Contents Only';
    public const STRUCTURE_ONLY         = 'Structure Only';
    public const CONTENTS_AND_STRUCTURE = 'Contents and Structure';

    protected static $values = [
        'CONTENTS_ONLY'          => self::CONTENTS_ONLY,
        'STRUCTURE_ONLY'         => self::STRUCTURE_ONLY,
        'CONTENTS_AND_STRUCTURE' => self::CONTENTS_AND_STRUCTURE,
    ];
}
