<?php

namespace Tests\Unit\Jobs\Requests;

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactCategory;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Jobs\Enums\RecurrenceRules;
use App\Components\Jobs\Models\JobService;
use App\Components\Locations\Models\Location;
use App\Http\Requests\Jobs\CreateRecurringJobRequest;
use Tests\Unit\RequestValidatorTestCase;

/**
 * Class CreateRecurringJobRequestTest
 *
 * @package Tests\Unit\Jobs\Requests
 * @group   jobs
 * @group   request-validation
 */
class CreateRecurringJobRequestTest extends RequestValidatorTestCase
{
    /**
     * @var array
     */
    protected $attributes = [];

    protected $requestClass = CreateRecurringJobRequest::class;

    public function setUp()
    {
        parent::setUp();

        $service = factory(JobService::class)->create();
        /** @var  Contact $personContact */
        $contactCategory = factory(ContactCategory::class)->create([
            'type' => ContactCategoryTypes::INSURER,
        ]);
        /** @var  Contact $companyContact */
        $companyContact = factory(Contact::class)->create([
            'contact_type'        => ContactTypes::COMPANY,
            'contact_category_id' => $contactCategory->id,
        ]);
        $address        = factory(Address::class)->create();
        $location       = factory(Location::class)->create();

        $rules            = RecurrenceRules::values();
        $this->attributes = [
            'recurrence_rule'      => $rules[$this->faker->numberBetween(0, count($rules) - 1)],
            'job_service_id'       => $service->id,
            'insurer_id'           => $companyContact->id,
            'site_address_id'      => $address->id,
            'assigned_location_id' => $location->id,
            'owner_location_id'    => $location->id,
            'description'          => $this->faker->sentence,
        ];
    }

    public function testRuleSet()
    {
        $this->assertRuleExistsForField('recurrence_rule');
        $this->assertRuleExistsForField('job_service_id');
        $this->assertRuleExistsForField('insurer_id');
        $this->assertRuleExistsForField('site_address_id');
        $this->assertRuleExistsForField('owner_location_id');
        $this->assertRuleExistsForField('description');
    }

    public function testValidationSuccess()
    {
        $this->assertValidationSuccess($this->attributes);
    }

    public function testValidationShouldFailWithoutRequredFields()
    {
        $this->assertValidationFails([]);
    }

    public function testValidationShouldFailWithInvalidRecurrenceRule()
    {
        $this->attributes['recurrence_rule'] = 'INVALID';
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidService()
    {
        $this->attributes['job_service_id'] = 0;
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidInsurer()
    {
        $this->attributes['insurer_id'] = 0;
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidAddress()
    {
        $this->attributes['site_address_id'] = 0;
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidOwnerLocation()
    {
        $this->attributes['owner_location_id'] = 0;
        $this->assertValidationFails($this->attributes);
    }
}
