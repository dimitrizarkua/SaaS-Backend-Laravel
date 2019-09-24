<?php

namespace Tests\Unit\Helpers;

use App\Helpers\DateHelper;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Class DateHelperTest
 *
 * @package Tests\Unit\Helpers
 * @group   helpers
 */
class DateHelperTest extends TestCase
{
    public function testAddWorkHours()
    {
        //2019-03-01 08:00:00
        $startDate = Carbon::create(2019, 3, 4, 8, 0, 0);

        $endDate = DateHelper::addWorkHours($startDate, 10);

        //should be 2019-03-02 10:00:00
        $expectedDate = Carbon::create(2019, 3, 5, 10, 0, 0);
        self::assertEquals($expectedDate, $endDate);
    }

    public function testAddWorkHoursWithAfterHours()
    {
        //2019-03-01 09:00:00
        $startDate = Carbon::create(2019, 3, 1, 9, 0, 0);

        $endDate = DateHelper::addWorkHours($startDate, 10, true);

        //should be 2019-03-02 19:00:00
        $expectedDate = Carbon::create(2019, 3, 1, 19, 0, 0);
        self::assertEquals($expectedDate, $endDate);
    }

    public function testAddWorkHoursForTimeCloseForEndOfDay()
    {
        //Friday 2019-03-01 16:00:00
        $startDate = Carbon::create(2019, 3, 1, DateHelper::END_DAY_HOUR - 1, 0, 0);

        $endDate = DateHelper::addWorkHours($startDate, 10);

        //Tuesday 2019-03-05 09:00:00
        $expectedDate = Carbon::create(2019, 3, 5, DateHelper::START_DAY_HOUR + 1, 0, 0);
        self::assertEquals($expectedDate, $endDate);
    }

    public function testAddWorkHoursWithMinutes()
    {
        //2019-03-01 08:01:00
        $startDate = Carbon::create(2019, 3, 4, DateHelper::START_DAY_HOUR, 1, 0);

        $endDate = DateHelper::addWorkHours($startDate, 8);

        //should be 2019-03-02 08:01:00
        $expectedDate = Carbon::create(2019, 3, 4, DateHelper::END_DAY_HOUR - 1, 1, 0);
        self::assertEquals($expectedDate, $endDate);
    }

    public function testAddWorkHoursForTimeCloseForEndOfDayWithMinutes()
    {
        //Monday 2019-03-01 10:10:00
        $startDate = Carbon::create(2019, 3, 4, DateHelper::START_DAY_HOUR + 2, 10, 0);

        $endDate = DateHelper::addWorkHours($startDate, 7);

        //Tuesday be 2019-03-02 08:10:00
        $expectedDate = Carbon::create(2019, 3, 5, DateHelper::START_DAY_HOUR, 10, 0);
        self::assertEquals($expectedDate, $endDate);
    }

    public function testAddWorkHoursForTimeCloseForWeekend()
    {
        //Friday 2019-03-01 08:00:00
        $startDate = Carbon::create(2019, 3, 1, DateHelper::START_DAY_HOUR, 0, 0);

        $endDate = DateHelper::addWorkHours($startDate, 9);

        //Monday 2019-03-04 08:00:00
        $expectedDate = Carbon::create(2019, 3, 4, DateHelper::START_DAY_HOUR + 1, 0, 0);
        self::assertEquals($expectedDate, $endDate);
    }

    public function testGetFinancialYearStart()
    {
        $today    = Carbon::create(2018, DateHelper::FINANCIAL_YEAR_START_MONTH_NUMBER - 1, 4);
        $expected = Carbon::create(2017, DateHelper::FINANCIAL_YEAR_START_MONTH_NUMBER, 1);
        Carbon::setTestNow($today);
        self::assertEquals($expected, DateHelper::getFinancialYearStart());

        $today    = Carbon::create(2018, DateHelper::FINANCIAL_YEAR_START_MONTH_NUMBER + 1, 4);
        $expected = Carbon::create(2018, DateHelper::FINANCIAL_YEAR_START_MONTH_NUMBER, 1);
        Carbon::setTestNow($today);
        self::assertEquals($expected, DateHelper::getFinancialYearStart());
    }
}
