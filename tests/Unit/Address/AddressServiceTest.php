<?php

namespace Tests\Unit\Address;

use App\Components\Addresses\Interfaces\AddressServiceInterface;
use App\Components\Addresses\Models\Address;
use App\Components\Addresses\Models\Country;
use App\Components\Addresses\Models\State;
use App\Components\Addresses\Models\Suburb;
use Illuminate\Container\Container;
use Tests\TestCase;
use App\Components\Addresses\Helpers\CountryHelper;
use App\Components\Addresses\Helpers\StatesHelper;

/**
 * Class AddressServiceTest
 *
 * @package Tests\Unit\Address
 * @group   addresses
 * @group   address-service
 */
class AddressServiceTest extends TestCase
{
    /**
     * @var \App\Components\Addresses\Interfaces\AddressServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();
        $this->service = Container::getInstance()
            ->make(AddressServiceInterface::class);
    }

    public function testParseAddressReturnSuccessResult()
    {
        $countryModel = Country::create([
            'name'            => 'Australia',
            'iso_alpha2_code' => CountryHelper::getAlpha2Code('Australia'),
            'iso_alpha3_code' => CountryHelper::getAlpha3Code('Australia'),
        ]);

        $stateModel = State::create([
            'country_id' => $countryModel->id,
            'code'       => 'VIC',
            'name'       => StatesHelper::getStateNameByCode('VIC'),
        ]);

        $suburbModel = Suburb::create([
            'state_id' => $stateModel->id,
            'name'     => 'Newport',
            'postcode' => 3015,
        ]);

        $result = $this->service->parseAddress('143 Mason St, Newport VIC 3015');
        self::assertInstanceOf(Address::class, $result);
        self::assertEquals('143 Mason St', $result->address_line_1);
        self::assertNotNull($result->suburb);
        self::assertEquals($suburbModel->name, $result->suburb->name);
        self::assertNotNull($result->suburb->state);
        self::assertEquals($stateModel->code, $result->suburb->state->code);
        self::assertNotNull($result->suburb->state->country);
        self::assertEquals($countryModel->name, $result->suburb->state->country->name);
    }

    public function testParseAddressSuccessResultWithNonParsableString()
    {
        $result = $this->service->parseAddress('143 Mason St');
        self::assertInstanceOf(Address::class, $result);
        self::assertEquals('143 Mason St', $result->address_line_1);
        self::assertNull($result->suburb_id);
    }
}
