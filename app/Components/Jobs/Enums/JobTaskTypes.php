<?php

namespace App\Components\Jobs\Enums;

use vijinho\Enums\Enum;

/**
 * Class JobTaskTypes
 *
 * @package App\Components\Jobs\Enums
 */
class JobTaskTypes extends Enum
{
    public const FIRST_ATTENDANCE    = 'First Attendance';
    public const SECOND_ATTENDANCE   = 'Second Attendance';
    public const ON_GOING_ATTENDANCE = 'On-Going Attendance';
    public const EQUIPMENT_PICKUP    = 'Equipment Pickup';
    public const FINAL_ATTENDANCE    = 'Final Attendance';
    public const INITIAL_CONTACT_KPI = 'Initial Contact (KPI)';
    public const VAN_CLEAN           = 'Van Clean';

    protected static $values = [
        'FIRST_ATTENDANCE'    => self::FIRST_ATTENDANCE,
        'SECOND_ATTENDANCE'   => self::SECOND_ATTENDANCE,
        'ON_GOING_ATTENDANCE' => self::ON_GOING_ATTENDANCE,
        'EQUIPMENT_PICKUP'    => self::EQUIPMENT_PICKUP,
        'FINAL_ATTENDANCE'    => self::FINAL_ATTENDANCE,
        'INITIAL_CONTACT_KPI' => self::INITIAL_CONTACT_KPI,
        'VAN_CLEAN'           => self::VAN_CLEAN,
    ];
}
