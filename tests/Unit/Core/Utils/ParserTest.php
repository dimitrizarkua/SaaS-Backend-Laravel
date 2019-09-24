<?php

namespace Tests\Unit\Core\Utils;

use App\Core\Utils\Parser;
use Tests\TestCase;

/**
 * Class ParserTest
 *
 * @package Tests\Unit\Core\Utils
 */
class ParserTest extends TestCase
{
    public function testClaimNumberParsing()
    {
        $count = $this->faker->numberBetween(10, 30);

        for ($i = 0; $i < $count; $i++) {
            $input = $this->faker->regexify(Parser::CLAIM_NUMBER_VARIATIONS_REGEX);
            self::assertNotNull(Parser::parseClaimNumber($input));
        }
    }

    public function testJobIdParsing()
    {
        $count = $this->faker->numberBetween(10, 30);

        for ($i = 0; $i < $count; $i++) {
            $input = $this->faker->regexify(Parser::STEAMATIC_JOB_ID_VARIATIONS_REGEX);
            self::assertNotNull(Parser::parseJobId($input));
        }
    }

    public function testJobServiceTypeParsing()
    {
        $count = $this->faker->numberBetween(10, 30);

        for ($i = 0; $i < $count; $i++) {
            $input = $this->faker->regexify(Parser::JOB_SERVICE_TYPE_VARIATIONS_REGEX);
            self::assertNotNull(Parser::parseJobServiceType($input));
        }
    }

    public function testAddressParsing()
    {
        $count = $this->faker->numberBetween(10, 30);

        for ($i = 0; $i < $count; $i++) {
            $input = $this->faker->regexify('[Aa]ddress *(\:| ) *') . $this->faker->streetAddress;
            self::assertNotNull(Parser::parseAddress($input));
        }
    }

    public function testCustomerParsing()
    {
        $count = $this->faker->numberBetween(10, 30);

        for ($i = 0; $i < $count; $i++) {
            $input = $this->faker->regexify('[Cc]ustomer *(\:| ) *') . $this->faker->name;
            self::assertNotNull(Parser::parseCustomer($input));
        }
    }

    public function testContactPhoneNumberParsing()
    {
        $count = $this->faker->numberBetween(10, 30);

        for ($i = 0; $i < $count; $i++) {
            $input = $this->faker->regexify('[Cc]ontact +(\#|no?\.?|number\:?|phone\:?| ) *');
            $input .= $this->faker->phoneNumber;
            self::assertNotNull(Parser::parseContactPhone($input));
        }
    }
}
