<?php

namespace App\Helpers;

use App\Components\UsageAndActuals\Models\Holiday;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Class DateHelper
 *
 * @package App\Helpers
 */
class DateHelper
{
    public const START_DAY_HOUR                    = 8;
    public const END_DAY_HOUR                      = 17;
    public const FINANCIAL_YEAR_START_MONTH_NUMBER = 7;
    public const WORKING_DAYS_IN_WEEK              = 5;

    /**
     * Add work hours to given date time. This method is skipping weekends.
     *
     * @param Carbon $startDate         Start date time object.
     * @param int    $hours             Hours count to be added to startDate.
     * @param bool   $includeAfterHours Indicates whether calculation should include after hours. If set to false,
     *                                  only working hours will be counted.
     *
     * @return Carbon
     */
    public static function addWorkHours(Carbon $startDate, int $hours, bool $includeAfterHours = false): Carbon
    {
        $endDate = clone $startDate;
        $endDate->second(0);

        if (true === $includeAfterHours) {
            $endDate->addHours($hours);

            return $endDate;
        }

        if ($hours > 8) {
            $countOfDays  = intdiv($hours, 8);
            $countOfHours = $hours - ($countOfDays * 8);
        } else {
            $countOfDays  = 0;
            $countOfHours = $hours;
        }

        self::addDays($endDate, $countOfDays);
        $endDate->addHours($countOfHours);

        if ($endDate->hour > self::END_DAY_HOUR) {
            $diffHours = $endDate->hour - self::END_DAY_HOUR;
            $minutes   = $endDate->minute;
            self::addDays($endDate)
                ->setTime(self::START_DAY_HOUR, $minutes)
                ->addHour($diffHours);
        }

        if ($endDate->hour === self::END_DAY_HOUR and 0 !== $endDate->minute) {
            $minutes = $endDate->minute;
            self::addDays($endDate)
                ->setTime(self::START_DAY_HOUR, $minutes);
        }

        return $endDate;
    }

    /**
     * Calculate number of minutes for each tier rate for datetime interval.
     *
     * @param \Illuminate\Support\Carbon                         $startTime
     * @param \Illuminate\Support\Carbon                         $endTime
     *
     * @param \Illuminate\Database\Eloquent\Collection|Holiday[] $holidays
     *
     * @return array
     */
    public static function calculateWorkTimeByInterval(Carbon $startTime, Carbon $endTime, Collection $holidays): array
    {
        $currentStartTime = clone $startTime;

        $firstTierAmount  = 0;
        $secondTierAmount = 0;
        $thirdTierAmount  = 0;
        $fourthTierAmount = 0;

        do {
            $currentEndTime = clone $startTime;
            /** @var Carbon $endTime */
            $currentEndTime = $currentEndTime->endOfDay()->min($endTime);
            if ($holidays->first(function ($item) use ($currentStartTime) {
                return $item->date->format('Y-m-d') == $currentStartTime->format('Y-m-d');
            })) {
                $fourthTierAmount += $endTime->diffInMinutes($startTime);
                //Add payment for work with fourth tier rate
            } elseif ($currentStartTime->isWeekend()) {
                $thirdTierAmount += $endTime->diffInMinutes($startTime);
                //Add payment for work with third tier rate
            } else {
                $workingInterval = self::getIntersectionInMinutes(
                    $startTime,
                    $endTime,
                    Carbon::create(
                        $currentStartTime->year,
                        $currentStartTime->month,
                        $currentStartTime->day,
                        self::START_DAY_HOUR
                    ),
                    Carbon::create(
                        $currentStartTime->year,
                        $currentStartTime->month,
                        $currentStartTime->day,
                        self::END_DAY_HOUR
                    )
                );

                //Add payment for work with second tier rate
                $secondTierAmount += ($endTime->diffInMinutes($startTime) - $workingInterval);
                //Add payment for work with first tier rate
                $firstTierAmount += $workingInterval;
            }

            $currentStartTime->addDay()->startOfDay();
        } while ($currentEndTime > $endTime);

        return [
            'firstTierAmount'  => $firstTierAmount,
            'secondTierAmount' => $secondTierAmount,
            'thirdTierAmount'  => $thirdTierAmount,
            'fourthTierAmount' => $fourthTierAmount,
        ];
    }

    /**
     * Returns intersection between intervals.
     *
     * @param \Illuminate\Support\Carbon $s1 Beginning of the first interval.
     * @param \Illuminate\Support\Carbon $e1 End of the first interval.
     * @param \Illuminate\Support\Carbon $s2 Beginning of the second interval.
     * @param \Illuminate\Support\Carbon $e2 End of the second interval.
     *
     * @return integer
     */
    private static function getIntersectionInMinutes(Carbon $s1, Carbon $e1, Carbon $s2, Carbon $e2): int
    {
        if ($s1->between($s2, $e2)) {
            return $s1->diffInMinutes(min($e1, $e2));
        }

        if ($e1->between($s2, $e2)) {
            return $s2->diffInMinutes($e1);
        }

        return 0;
    }

    /**
     * Add days to given date. This method is skipping a weekends.
     *
     * @param Carbon $date
     * @param int    $days
     *
     * @return Carbon
     */
    private static function addDays(Carbon $date, int $days = 1): Carbon
    {
        for ($i = 0; $i < $days; $i++) {
            $date->addDay();
            while ($date->isWeekend()) {
                $date->addDay();
            }
        }

        return $date;
    }

    /**
     * Returns start of current financial year (July 1st).
     *
     * @return Carbon
     */
    public static function getFinancialYearStart(): Carbon
    {
        return new Carbon('first day of ' .
            date("F", mktime(0, 0, 0, self::FINANCIAL_YEAR_START_MONTH_NUMBER)) .
            (Carbon::now()->month >= self::FINANCIAL_YEAR_START_MONTH_NUMBER ? ' this' : '  last') . ' year');
    }
}
