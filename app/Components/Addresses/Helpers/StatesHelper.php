<?php

namespace App\Components\Addresses\Helpers;

/**
 * Class StatesHelper
 *
 * @package App\Components\Addresses
 */
class StatesHelper
{
    /**
     * Australia states list.
     *
     * @var array
     */
    public static $stateList = [
        'NSW' => 'New South Wales',
        'QLD' => 'Queensland',
        'SA'  => 'South Australia',
        'TAS' => 'Tasmania',
        'VIC' => 'Victoria',
        'WA'  => 'Western Australia',
    ];

    /**
     * Returns state name by its code.
     *
     * @param string $stateCode
     *
     * @return string
     */
    public static function getStateNameByCode(string $stateCode): string
    {
        if (!array_key_exists($stateCode, self::$stateList)) {
            throw new \InvalidArgumentException('Invalid state code');
        }

        return self::$stateList[$stateCode];
    }

    /**
     * Returns state code by its name.
     *
     * @param string $stateName
     *
     * @return string
     */
    public static function getStateCodeByStateName(string $stateName): string
    {
        $states = array_flip(self::$stateList);
        if (!array_key_exists($stateName, $states)) {
            throw new \InvalidArgumentException('Invalid state name');
        }

        return $states[$stateName];
    }
}
