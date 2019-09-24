<?php

namespace App\Components\Jobs\Enums;

use vijinho\Enums\Enum;

/**
 * Class JobStatuses
 *
 * @package App\Components\Jobs\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Job Statuses",
 *     enum={"New","On-Hold","In-Progress","Closed","Cancelled"}
 * )
 */
class JobStatuses extends Enum
{
    public const NEW         = 'New';
    public const ON_HOLD     = 'On-Hold';
    public const IN_PROGRESS = 'In-Progress';
    public const CLOSED      = 'Closed';
    public const CANCELLED   = 'Cancelled';

    protected static $values = [
        'NEW'         => self::NEW,
        'ON_HOLD'     => self::ON_HOLD,
        'IN_PROGRESS' => self::IN_PROGRESS,
        'CLOSED'      => self::CLOSED,
        'CANCELLED'   => self::CANCELLED,
    ];

    /**
     * List of active statuses.
     *
     * @var array
     */
    public static $activeStatuses = [
        self::IN_PROGRESS,
        self::ON_HOLD,
    ];

    /**
     * List of closed statuses.
     *
     * @var array
     */
    public static $closedStatuses = [
        self::CLOSED,
        self::CANCELLED,
    ];
}
