<?php

namespace Tests\Unit\Contacts\Requests;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Http\Requests\Contacts\CreateContactCategoryRequest;
use Tests\Unit\RequestValidatorTestCase;

/**
 * Class CreateContactCategoryRequestTest
 *
 * @package Tests\Unit\Contacts\Requests
 * @group   contacts
 * @group   request-validation
 */
class CreateContactCategoryRequestTest extends RequestValidatorTestCase
{
    protected $requestClass = CreateContactCategoryRequest::class;

    /**
     * @var array
     */
    protected $attributes = [];

    public function setUp()
    {
        parent::setUp();

        $this->attributes = [
            'name' => $this->faker->word,
            'type' => $this->faker->randomElement(ContactCategoryTypes::values()),
        ];
    }

    public function testRuleSet()
    {
        $this->assertRuleExistsForField('name');
        $this->assertRuleExistsForField('type');
    }

    public function testValidationSuccess()
    {
        $this->assertValidationSuccess($this->attributes);
    }

    public function testValidationShouldFailWithoutName()
    {
        unset($this->attributes['name']);
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithoutType()
    {
        unset($this->attributes['type']);
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidType()
    {
        $this->attributes['type'] = 'wrong-type';
        $this->assertValidationFails($this->attributes);
    }
}
