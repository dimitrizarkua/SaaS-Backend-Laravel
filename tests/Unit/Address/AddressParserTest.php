<?php

namespace Tests\Unit\Address;

use App\Components\Addresses\Parser\AddressParser;
use App\Components\Addresses\Parser\ParserResult;
use Tests\TestCase;

/**
 * Class AddressParserTest
 *
 * @package Tests\Unit\Address
 */
class AddressParserTest extends TestCase
{
    private $testAddress = '143 Mason St, Newport VIC 3015';

    public function testParserShouldReturnResult()
    {
        $result = AddressParser::parse($this->testAddress);
        self::assertInstanceOf(ParserResult::class, $result);
    }

    public function testResult()
    {
        $result = AddressParser::parse($this->testAddress);
        self::assertEquals(3015, $result->getPostCode());
        self::assertEquals('143 Mason St', $result->getAddressLine1());
        self::assertEquals('Newport', $result->getSuburb());
        self::assertEquals('VIC', $result->getStateCode());
    }

    public function testResultWithComas()
    {
        $result = AddressParser::parse('143 Mason St, Newport, VIC, 3015');
        self::assertEquals(3015, $result->getPostCode());
        self::assertEquals('143 Mason St', $result->getAddressLine1());
        self::assertEquals('Newport', $result->getSuburb());
        self::assertEquals('VIC', $result->getStateCode());
    }

    public function testResultWithOutComas()
    {
        $result = AddressParser::parse('143 Mason St Newport VIC 3015');
        self::assertEquals(3015, $result->getPostCode());
        self::assertEquals('143 Mason St', $result->getAddressLine1());
        self::assertEquals('Newport', $result->getSuburb());
        self::assertEquals('VIC', $result->getStateCode());
    }
}
