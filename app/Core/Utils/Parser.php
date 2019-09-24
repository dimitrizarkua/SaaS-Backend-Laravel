<?php

namespace App\Core\Utils;

/**
 * Class Parser
 *
 * @package App\Core\Utils
 */
class Parser
{
    // For matching

    const STEAMATIC_JOB_ID_REGEX = '/(?:steamatic\s*)?(?:job)\s*(?:\#|no?\.?|number)?\s*(\d+)\s?/mi';
    const CLAIM_NUMBER_REGEX     =
        '/claim(?:\s*(?:\#|no?|number))?\s*(?:\s|\.|\:)\s*((?<=\s|\.|\:)(?:\w|\-|_|\.)+)(?<!\.)\s?/mi';
    const JOB_SERVICE_TYPE_REGEX = '/(?:(?:job|service)\s*)type\s*:?\s*((?:\b\w+\b)+(?:\s\b\w+\b)*)/mi';
    const ADDRESS_REGEX          = '/address\s*:?\s*((?:\b[\w\.\,\-]+\b)+(?:[ \,\.] *\b[\'\w\.\,\-]+\b)*)\s?$/mi';
    const CUSTOMER_REGEX         = '/customer\s*:?\s*([a-z ,.\'-]+)\s?/mi';
    const CONTACT_PHONE_REGEX    =
        '/contact\s*(?:\#|no?\.?|number\:?|phone\:?)?\s*\+?((?:\(?\d+\)?[\- \.]*)+(?<! ))/mi';

    // For generating variations with faker

    const CLAIM_NUMBER_VARIATIONS_REGEX     = '[Cc]laim +(\#|[Nn]o?\.?|[Nn]umber) +[a-zA-Z0-9-]{3,20}';
    const STEAMATIC_JOB_ID_VARIATIONS_REGEX = '([Ss]teamatic +)?[Jj]ob +\# +[0-9]{1,20}';
    const JOB_SERVICE_TYPE_VARIATIONS_REGEX =
        '([Jj]ob [Tt]ype|[Ss]ervice [Tt]ype) *(\:| ) *(\b\w+\b)+( \b\w+\b){0,10}';

    /**
     * Tries to find job id in input string.
     *
     * @param string $input Input string.
     *
     * @return null|string
     */
    public static function parseJobId(string $input): ?string
    {
        if (preg_match_all(self::STEAMATIC_JOB_ID_REGEX, $input, $matches)) {
            return $matches[1][0];
        }

        return null;
    }

    /**
     * Tries to find claim number in input string.
     *
     * @param string|null $input Input string.
     *
     * @return null|string
     */
    public static function parseClaimNumber(?string $input): ?string
    {
        if (!empty($input) && preg_match_all(self::CLAIM_NUMBER_REGEX, $input, $matches)) {
            return $matches[1][0];
        }

        return null;
    }

    /**
     * Tries to find job service type in input string.
     *
     * @param string $input Input string.
     *
     * @return null|string
     */
    public static function parseJobServiceType(string $input): ?string
    {
        if (preg_match_all(self::JOB_SERVICE_TYPE_REGEX, $input, $matches)) {
            return $matches[1][0];
        }

        return null;
    }

    /**
     * Tries to find address string in input string.
     *
     * @param string $input Input string.
     *
     * @return null|string
     */
    public static function parseAddress(string $input): ?string
    {
        if (preg_match_all(self::ADDRESS_REGEX, $input, $matches)) {
            return $matches[1][0];
        }

        return null;
    }

    /**
     * Tries to find customer name in input string.
     *
     * @param string $input Input string.
     *
     * @return null|string
     */
    public static function parseCustomer(string $input): ?string
    {
        if (preg_match_all(self::CUSTOMER_REGEX, $input, $matches)) {
            return $matches[1][0];
        }

        return null;
    }

    /**
     * Tries to find contact phone number in input string.
     *
     * @param string $input Input string.
     *
     * @return null|string
     */
    public static function parseContactPhone(string $input): ?string
    {
        if (preg_match_all(self::CONTACT_PHONE_REGEX, $input, $matches)) {
            return $matches[1][0];
        }

        return null;
    }
}
