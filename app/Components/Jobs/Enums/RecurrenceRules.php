<?php

namespace App\Components\Jobs\Enums;

use vijinho\Enums\Enum;

/**
 * Class RecurrenceRules
 *
 * @package App\Components\Jobs\Enums
 */
class RecurrenceRules extends Enum
{
    /**
     * List of active statuses.
     *
     * @var array
     */
    public static $values = [
        'FREQ=DAILY',                         // Every day
        'FREQ=DAILY;BYHOUR=10,12,17',         // Every day at 10, 12 and 17
        'FREQ=DAILY;DTEND=20181221T033550',   // Every day until 2018-12-21T03-35-50
        'FREQ=WEEKLY',                        // Every week
        'FREQ=HOURLY',                        // Every hour
        'INTERVAL=4;FREQ=HOURLY',             // Every 4 hours
        'FREQ=WEEKLY;BYDAY=TU',               // Every week on Tuesday
        'FREQ=WEEKLY;BYDAY=MO,WE',            // Every week on Monday, Wednesday
        'FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR',   // Every weekday
        'INTERVAL=2;FREQ=WEEKLY',             // Every 2 weeks
        'FREQ=MONTHLY',                       // Every month
        'INTERVAL=6;FREQ=MONTHLY',            // Every 6 months
        'FREQ=YEARLY',                        // Every year
        'FREQ=MONTHLY;BYMONTHDAY=4',          // Every month on the 4th
        'FREQ=MONTHLY;BYMONTHDAY=-4',         // Every month on the 4th last
        'FREQ=MONTHLY;BYDAY=+3TU',            // Every month on the 3rd Tuesday
        'FREQ=MONTHLY;BYDAY=-3TU',            // Every month on the 3rd last Tuesday
        'FREQ=MONTHLY;BYDAY=-1MO',            // Every month on the last Monday
        'FREQ=MONTHLY;BYDAY=-2FR',            // Every month on the 2nd last Friday
        'FREQ=WEEKLY;COUNT=20',               // Every week for 20 times
    ];
}
