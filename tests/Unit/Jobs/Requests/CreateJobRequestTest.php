<?php

namespace Tests\Unit\Jobs\Requests;

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactCategory;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Jobs\Enums\ClaimTypes;
use App\Components\Jobs\Enums\JobCriticalityTypes;
use App\Components\Jobs\Models\JobService;
use App\Components\Locations\Models\Location;
use App\Http\Requests\Jobs\CreateJobRequest;
use Tests\Unit\RequestValidatorTestCase;

/**
 * Class CreateJobRequestTest
 *
 * @package Tests\Unit\Jobs\Requests
 * @group   jobs
 * @group   request-validation
 */
class CreateJobRequestTest extends RequestValidatorTestCase
{
    /**
     * @var array
     */
    protected $attributes = [];

    protected $requestClass = CreateJobRequest::class;

    public function setUp()
    {
        parent::setUp();

        $service = factory(JobService::class)->create();
        $contactCategory = factory(ContactCategory::class)->create([
            'type' => ContactCategoryTypes::INSURER,
        ]);
        /** @var  Contact $companyContact */
        $companyContact = factory(Contact::class)->create([
            'contact_type'        => ContactTypes::COMPANY,
            'contact_category_id' => $contactCategory->id,
        ]);
        $address       = factory(Address::class)->create();
        $location      = factory(Location::class)->create();

        $this->attributes = [
            'claim_number'             => $this->faker->word,
            'job_service_id'           => $service->id,
            'insurer_id'               => $companyContact->id,
            'site_address_id'          => $address->id,
            'site_address_lat'         => $this->faker->latitude,
            'site_address_lng'         => $this->faker->longitude,
            'assigned_location_id'     => $location->id,
            'owner_location_id'        => $location->id,
            'reference_number'         => $this->faker->word,
            'claim_type'               => $this->faker->randomElement(ClaimTypes::values()),
            'criticality'              => $this->faker->randomElement(JobCriticalityTypes::values()),
            'date_of_loss'             => $this->faker->date(),
            'initial_contact_at'       => $this->faker->date('Y-m-d\TH:i:s\Z'),
            'cause_of_loss'            => $this->faker->word,
            'description'              => $this->faker->sentence,
            'anticipated_revenue'      => $this->faker->randomFloat(2),
            'anticipated_invoice_date' => $this->faker->date(),
            'authority_received_at'    => $this->faker->date('Y-m-d\TH:i:s\Z'),
            'expected_excess_payment'  => $this->faker->randomFloat(2),
        ];
    }

    public function testRuleSet()
    {
        $this->assertRuleExistsForField('claim_number');
        $this->assertRuleExistsForField('job_service_id');
        $this->assertRuleExistsForField('insurer_id');
        $this->assertRuleExistsForField('site_address_id');
        $this->assertRuleExistsForField('site_address_lat');
        $this->assertRuleExistsForField('site_address_lng');
        $this->assertRuleExistsForField('assigned_location_id');
        $this->assertRuleExistsForField('owner_location_id');
        $this->assertRuleExistsForField('reference_number');
        $this->assertRuleExistsForField('claim_type');
        $this->assertRuleExistsForField('criticality');
        $this->assertRuleExistsForField('date_of_loss');
        $this->assertRuleExistsForField('initial_contact_at');
        $this->assertRuleExistsForField('cause_of_loss');
        $this->assertRuleExistsForField('description');
        $this->assertRuleExistsForField('anticipated_revenue');
        $this->assertRuleExistsForField('anticipated_invoice_date');
        $this->assertRuleExistsForField('authority_received_at');
        $this->assertRuleExistsForField('expected_excess_payment');
        $this->assertRuleExistsForField('expected_excess_payment');
    }

    public function testValidationSuccess()
    {
        $this->assertValidationSuccess($this->attributes);
    }

    public function testValidationShouldSuccessWithoutRequiredFields()
    {
        $this->assertValidationSuccess([]);
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

    public function testValidationShouldFailWithInvalidAddressLat()
    {
        $this->attributes['site_address_lat'] = -91;
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidAddressLng()
    {
        $this->attributes['site_address_lng'] = +181;
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidAssignedLocation()
    {
        $this->attributes['assigned_location_id'] = 0;
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidOwnerLocation()
    {
        $this->attributes['assigned_location_id'] = 0;
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidCliamType()
    {
        $this->attributes['claim_type'] = 'wrong_claim_type';
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidCriticality()
    {
        $this->attributes['criticality'] = 'wrong_criticality';
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidDateOfLoss()
    {
        $this->attributes['date_of_loss'] = 'this string is not date';
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidInitialContactAts()
    {
        //Wrong date format
        $this->attributes['initial_contact_at'] = $this->faker->date();
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidAnticipatedInvoiceDate()
    {
        $this->attributes['anticipated_invoice_date'] = 'this string is not date';
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidAuthorityReceivedAt()
    {
        $this->attributes['authority_received_at'] = $this->faker->date();
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidRevenue()
    {
        $this->attributes['anticipated_revenue'] = $this->faker->word;
        $this->assertValidationFails($this->attributes);
    }

    public function testValidationShouldFailWithInvalidExpectedExcessPayment()
    {
        $this->attributes['expected_excess_payment'] = $this->faker->word;
        $this->assertValidationFails($this->attributes);
    }
}
