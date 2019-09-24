<?php

namespace App\Components\AssessmentReports\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class AssessmentReportHeadingStyles
 *
 * @package App\Components\AssessmentReports\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Assessment report heading style",
 *     enum={"regular","bold","light","italic"},
 * )
 */
class AssessmentReportHeadingStyles extends Enum
{
    public const REGULAR = 'regular';
    public const BOLD    = 'bold';
    public const LIGHT   = 'light';
    public const ITALIC  = 'italic';

    protected static $values = [
        'REGULAR' => self::REGULAR,
        'BOLD'    => self::BOLD,
        'LIGHT'   => self::LIGHT,
        'ITALIC'  => self::ITALIC,
    ];
}
