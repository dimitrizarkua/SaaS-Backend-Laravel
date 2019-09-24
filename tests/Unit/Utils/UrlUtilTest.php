<?php

namespace Tests\Unit\Utils;

use App\Utils\Url;
use Tests\TestCase;

/**
 * Class UrlUtilTest
 *
 * @package Tests\Unit\Utils
 * @group   utils
 */
class UrlUtilTest extends TestCase
{
    public function testPathGenerationWithSlash()
    {
        $base = 'http://test.local';
        $path = '/test';
        $url  = Url::getFullUrl($path, null, $base);

        self::assertEquals('http://test.local/test', $url);
    }

    public function testPathGenerationWithoutSlash()
    {
        $base = 'http://test.local';
        $path = 'test';
        $url  = Url::getFullUrl($path, null, $base);

        self::assertEquals('http://test.local/test', $url);
    }

    public function testShouldCorrectlyAppendQueryParams()
    {
        $base  = 'http://test.local';
        $path  = 'test';
        $query = [
            'first'  => 'first',
            'second' => 'second',
        ];
        $url   = Url::getFullUrl($path, $query, $base);
        self::assertEquals('http://test.local/test?first=first&second=second', $url);
    }
}
