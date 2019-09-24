<?php

namespace Tests\Unit\Rule;

use App\Rules\LastName;

/**
 * Class LastNameRuleTest
 *
 * @package Tests\Unit\Rule
 */
class LastNameRuleTest extends RuleTestCase
{
    protected $ruleClassName = LastName::class;

    public function testValid()
    {
        self::assertTrue($this->rule->passes('attribute', 'Validlastname'));
    }

    public function testInValid()
    {
        self::assertFalse($this->rule->passes('attribute', 'invalid_last_name'));
    }
}
