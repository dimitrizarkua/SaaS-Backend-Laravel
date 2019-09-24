<?php

namespace App\Utils;

use League\Uri\Components\Path;
use League\Uri\Components\Query;

/**
 * Class Url
 *
 * @package App\Utils
 */
class Url
{
    /**
     * Returns full url for specific path and query parameters.
     *
     * @param string     $path
     * @param array|null $queryData
     * @param string     $baseUrl
     *
     * @return string
     */
    public static function getFullUrl(string $path = null, array $queryData = null, string $baseUrl = null): string
    {
        $url = $baseUrl ?: env('FRONTEND_APP_URL', 'http://localhost');

        if (null !== $path) {
            $pathInstance = new Path($path);
            $url          .= $pathInstance->withLeadingSlash();
        }

        if (null !== $queryData) {
            $url .= '?' . Query::createFromPairs($queryData)->getContent();
        }

        return (string)$url;
    }
}
