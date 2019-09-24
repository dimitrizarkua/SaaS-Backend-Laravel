<?php

namespace Tests\Unit;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Class RequestValidatorTestCase
 * Class for testing request validation objects that are extends ApiRequest class.
 *
 * @package Tests\Unit
 */
class RequestValidatorTestCase extends TestCase
{
    /**
     * Class name of testing request instance
     *
     * @var string
     */
    protected $requestClass;

    /**
     * @var
     */
    protected $fails;

    /**
     * @var \App\Models\User
     */
    protected $user;

    /**
     * Assert that rule set has rule for given field.
     *
     * @param string $field
     */
    public function assertRuleExistsForField(string $field): void
    {
        $this->assertArrayHasKey($field, $this->getRules());
    }

    /**
     * Assert that validation successfully passed for given attributes.
     *
     * @param array $attributes Attributes to validate.
     */
    public function assertValidationSuccess(array $attributes): void
    {
        $this->assertFalse($this->validate($attributes));
    }

    /**
     * Assert that validation failed for given attributes.
     *
     * @param array $attributes Attributes to validate.
     */
    public function assertValidationFails(array $attributes)
    {
        $this->assertTrue($this->validate($attributes));
    }

    /**
     * Validates given attributes.
     *
     * @param array $attributes Attributes to validate.
     *
     * @return bool
     */
    protected function validate(array $attributes): bool
    {
        $validator = Validator::make($attributes, $this->getRules());

        return $validator->fails();
    }

    /**
     * Returns rule set from request class.
     *
     * @return array
     */
    protected function getRules()
    {
        if (null === $this->requestClass) {
            $this->fail('Property request is not specified for this test');
        }

        $request = new $this->requestClass;

        if (!$request instanceof ApiRequest) {
            $this->fail('This test case allows to test only ApiRequest entities');
        }

        if (null !== $this->user) {
            $request->setUserResolver(function () {
                return $this->user;
            });
        }

        return $request->rules();
    }
}
