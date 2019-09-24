<?php

namespace App\Components\Addresses\Services;

use App\Components\Addresses\Enums\AddressEndpointsLimits;
use App\Components\Addresses\Interfaces\AddressServiceInterface;
use App\Components\Addresses\Interfaces\HasAddressesInterface;
use App\Components\Addresses\Models\Address;
use App\Components\Addresses\Models\Country;
use App\Components\Addresses\Models\State;
use App\Components\Addresses\Models\Suburb;
use App\Components\Addresses\Parser\AddressParser;
use App\Components\Locations\Models\LocationSuburb;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AddressService
 *
 * @package App\Components\Addresses\Services
 */
class AddressService implements AddressServiceInterface
{
    /**
     * Attach address entity that has addresses.
     *
     * @param \App\Components\Addresses\Interfaces\HasAddressesInterface $model
     * @param \App\Components\Addresses\Models\Address                   $address
     */
    public function addAddress(HasAddressesInterface $model, Address $address): void
    {
        $model->addAddress($address);
    }

    /**
     * Parse string to address model.
     *
     * @param string $address Address string.
     * @param string $country Country for which method should parse address.
     *
     * @throws \Throwable
     * @return \App\Components\Addresses\Models\Address
     */

    public function parseAddress(string $address, string $country = 'Australia'): Address
    {
        $parseResult = AddressParser::parse($address, $country);

        if (null !== $parseResult->getSuburb()) {
            try {
                $countryModel = Country::where(['name' => $country])->firstOrFail();

                $stateModel = State::where([
                    'country_id' => $countryModel->id,
                    'code'       => $parseResult->getStateCode(),
                ])->firstOrFail();

                $suburbModel = Suburb::where([
                    'state_id' => $stateModel->id,
                    'name'     => $parseResult->getSuburb(),
                    'postcode' => $parseResult->getPostCode(),
                ])->firstOrFail();

                $addressModel = Address::firstOrNew([
                    'address_line_1' => $parseResult->getAddressLine1(),
                    'suburb_id'      => $suburbModel->id,
                ]);
            } catch (\Exception $e) {
                $addressModel = Address::firstOrNew([
                    'address_line_1' => $address,
                ]);
            }
        } else {
            $addressModel = Address::firstOrNew([
                'address_line_1' => $address,
            ]);
        }

        return $addressModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressLocations(Address $address): Collection
    {
        $locations = LocationSuburb::where(['suburb_id' => $address->suburb_id])->get();

        return $locations;
    }

    /**
     * {@inheritdoc}
     */
    public function searchSuburbs(array $filters): array
    {
        if (!isset($filters['count'])) {
            $filters['count'] = AddressEndpointsLimits::DEFAULT_SUBURBS_COUNT;
        }
        if ($filters['count'] > AddressEndpointsLimits::MAX_SUBURBS_COUNT) {
            $filters['count'] = AddressEndpointsLimits::MAX_SUBURBS_COUNT;
        }

        $results = Suburb::filter($filters)->raw();

        return mapElasticResults($results);
    }
}
