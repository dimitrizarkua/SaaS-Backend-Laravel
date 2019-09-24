<?php

namespace App\Components\Addresses\Helpers;

use League\ISO3166\Exception\OutOfBoundsException;
use League\ISO3166\ISO3166;

/**
 * Class CountryHelper
 *
 * @package App\Components\Addresses
 */
class CountryHelper
{
    /**
     * Checks whether is given country name exists.
     *
     * @param string $countyName Country name.
     *
     * @return bool
     */
    public static function isCountryExists(string $countyName): bool
    {
        try {
            self::getCountryData($countyName);
        } catch (OutOfBoundsException $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns iso alpha2 country code.
     *
     * @param string $countyName Country name.
     *
     * @return string
     */
    public static function getAlpha2Code(string $countyName): string
    {
        $data = self::getCountryData($countyName);

        return $data['alpha2'];
    }

    /**
     * Returns iso alpha3 country code.
     *
     * @param string $countyName Country name.
     *
     * @return string
     */
    public static function getAlpha3Code(string $countyName): string
    {
        $data = self::getCountryData($countyName);

        return $data['alpha3'];
    }


    /**
     * Return country information.
     *
     * @param string $countyName Country name.
     *
     * @return array
     */
    private static function getCountryData(string $countyName)
    {
        return (new ISO3166())->name($countyName);
    }
}
