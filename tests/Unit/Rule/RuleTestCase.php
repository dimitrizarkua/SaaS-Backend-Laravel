<?php

namespace Tests\Unit\Rule;

use Tests\TestCase;

/**
 * Class RuleTestCase
 *
 * @package Tests\Unit\Rule
 */
class RuleTestCase extends TestCase
{
    /**
     * Class name of the testing rule.
     *
     * @var string
     */
    protected $ruleClassName;
    /**
     * @var \Illuminate\Contracts\Validation\Rule
     */
    protected $rule;

    public function setUp()
    {
        parent::setUp();
        $this->rule = new $this->ruleClassName;
    }
}
