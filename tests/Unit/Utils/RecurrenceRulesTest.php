<?php

namespace Tests\Unit\Utils;

use DateTime;
use Recurr\Exception\InvalidArgument;
use Recurr\Exception\InvalidRRule;
use Recurr\Exception\InvalidWeekday;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Tests\TestCase;

/**
 * Class RecurrenceRulesTest
 *
 * @package Tests\Unit\Utils
 * @group   utils
 */
class RecurrenceRulesTest extends TestCase
{
    const RECURR_LIMIT = 732;
    /**
     * @throws InvalidArgument
     * @throws InvalidRRule
     */
    public function testRuleFromExample()
    {
        $startDate   = new DateTime('2013-06-12 20:00:00');

        $rule = (new Rule)
            ->setStartDate($startDate)
            ->setFreq('DAILY')
            ->setByDay(['MO', 'TU'])
            ->setUntil(new DateTime('2017-12-31'));

        self::assertEquals('FREQ=DAILY;UNTIL=20171231T000000;BYDAY=MO,TU', $rule->getString());
    }

    /**
     * @throws InvalidArgument
     * @throws InvalidRRule
     * @throws InvalidWeekday
     */
    public function testRuleCountTasksBetweenDate()
    {
        $startDate   = new DateTime('2018-12-01');

        $rule = (new Rule)
            ->setStartDate($startDate)
            ->setFreq('DAILY')
            ->setUntil(new DateTime('2018-12-31'));

        $transformer = new ArrayTransformer;
        $events = $transformer->transform($rule);

        self::assertEquals(31, count($events));
    }

    /**
     * @throws InvalidArgument
     * @throws InvalidRRule
     * @throws InvalidWeekday
     */
    public function testCheckGapLessThanOneDay()
    {
        $startDate   = new DateTime('2018-12-01');

        $rule = (new Rule)
            ->setStartDate($startDate)
            ->setFreq('DAILY')
            ->setUntil(new DateTime('2018-12-31'));

        $checkDate = new DateTime('2018-12-20');

        $transformer = new ArrayTransformer;
        $events = $transformer->transform($rule);

        self::assertEquals($events[19]->getStart(), $checkDate);
    }

    /**
     * @throws InvalidArgument
     * @throws InvalidRRule
     * @throws InvalidWeekday
     */
    public function testInfinityRule()
    {
        $startDate   = new DateTime('2018-12-01');

        $rule = (new Rule)
            ->setStartDate($startDate)
            ->setFreq('DAILY');

        $transformer = new ArrayTransformer;
        $events = $transformer->transform($rule);

        self::assertEquals(count($events), self::RECURR_LIMIT);
    }
}
