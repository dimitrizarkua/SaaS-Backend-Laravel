<?php

namespace App\Components\Addresses\Parser;

/**
 * Class AddressParser
 *
 * @package App\Components\Addresses\Parser
 */
class AddressParser
{
    public static function parse(string $address, string $country = 'Australia'): ParserResult
    {
        $address = trim($address);

        $expression = '/^([0-9\/]+\s[a-zA-Z ]+)(?:,|\s)\s?+([a-zA-Z]+)(?:,|\s)\s?([A-Z]{2,3})(?:,|\s)\s?(\d{4})/';
        preg_match($expression, $address, $match);

        $addressLine1 = $match[1] ?? $address;
        $suburb       = $match[2] ?? null;
        $stateCode    = $match[3] ?? null;
        $postCode     = $match[4] ?? null;

        return new ParserResult(
            $addressLine1,
            $suburb,
            $stateCode,
            $postCode,
            $country
        );
    }
}
