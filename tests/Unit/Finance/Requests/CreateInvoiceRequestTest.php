<?php

namespace Tests\Unit\Finance\Requests;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Locations\Models\Location;
use App\Http\Requests\Finance\CreateInvoiceRequest;
use Carbon\Carbon;
use App\Models\User;
use Tests\Unit\RequestValidatorTestCase;

/**
 * Class CreateInvoiceRequestTest
 *
 * @package Tests\Unit\Finance\Requests
 *
 * @group   invoices
 * @group   finance
 * @group   request-validation
 *
 */
class CreateInvoiceRequestTest extends RequestValidatorTestCase
{
    /**
     * @var array
     */
    protected $attributes = [];

    protected $requestClass = CreateInvoiceRequest::class;

    public function setUp()
    {
        parent::setUp();

        $location   = factory(Location::class)->create();
        $this->user = factory(User::class)->create();
        $this->user->locations()->attach($location);

        $this->attributes = [
            'location_id'                => $location->id,
            'accounting_organization_id' => factory(AccountingOrganization::class)->create()->id,
            'recipient_contact_id'       => factory(Contact::class)->create()->id,
            'recipient_address'          => 'Some address',
            'recipient_name'             => 'Some address',
            'date'                       => Carbon::now()->format('Y-m-d'),
            'due_at'                     => Carbon::now()->addDay()->format('Y-m-d\TH:i:s\Z'),
        ];
    }

    public function testValidationSuccess()
    {
        $this->assertValidationSuccess($this->attributes);
    }

    public function testValidationShouldBeFailedWithWrongLocation()
    {
        //Location that user does not belongs to.
        $this->attributes['location_id'] = factory(Location::class)->create()->id;
        $this->assertValidationFails($this->attributes);
    }
}
