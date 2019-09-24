<?php

namespace Tests\Unit\Rule;

use App\Rules\FirstName;

/**
 * Class FirstNameRuleTest
 *
 * @package Tests\Unit\Rule
 */
class FirstNameRuleTest extends RuleTestCase
{
    protected $ruleClassName = FirstName::class;

    public function testValid()
    {
        self::assertTrue($this->rule->passes('attribute', 'Validfirstname'));
    }

    public function testInValid()
    {
        self::assertFalse($this->rule->passes('attribute', 'invalid_first_name'));
    }
}
