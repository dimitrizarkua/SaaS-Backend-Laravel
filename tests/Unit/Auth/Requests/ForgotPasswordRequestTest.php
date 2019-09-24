<?php

namespace Tests\Unit\Auth\Requests;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use Tests\Unit\RequestValidatorTestCase;

/**
 * Class ForgotPasswordRequestTest
 *
 * @package Tests\Unit\Auth\Requests
 * @group   auth
 * @group   request-validation
 */
class ForgotPasswordRequestTest extends RequestValidatorTestCase
{
    protected $requestClass = ForgotPasswordRequest::class;

    /**
     * @var array
     */
    protected $attributes = [];

    public function setUp()
    {
        parent::setUp();

        $this->attributes = [
            'email' => $this->faker->email,
        ];
    }

    public function testRuleSet()
    {
        $this->assertRuleExistsForField('email');
    }

    public function testValidationSuccess()
    {
        $this->assertValidationSuccess($this->attributes);
    }

    public function testValidationShouldFailWithoutEmail()
    {
        $this->assertValidationFails([]);
    }

    public function testValidationShouldFailWithInvalidEmail()
    {
        $this->attributes['email'] = 'wrong-email';
        $this->assertValidationFails($this->attributes);
    }
}
