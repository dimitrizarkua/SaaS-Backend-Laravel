<?php

namespace App\Components\Addresses\Interfaces;

use App\Components\Addresses\Models\Address;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface AddressServiceInterface
 *
 * @package App\Components\Addresses\Interfaces
 */
interface AddressServiceInterface
{
    /**
     * Attach address entity that has addresses.
     *
     * @param \App\Components\Addresses\Interfaces\HasAddressesInterface $model
     * @param \App\Components\Addresses\Models\Address                   $address
     */
    public function addAddress(HasAddressesInterface $model, Address $address): void;

    /**
     * Parse string to address model.
     *
     * @param string $address Address string.
     * @param string $country Country for which method should parse address.
     *
     * @return \App\Components\Addresses\Models\Address
     */
    public function parseAddress(string $address, string $country = 'Australia'): Address;

    /**
     * Get filtered set of suburbs.
     *
     * @param array $filters
     *
     * @return array
     */
    public function searchSuburbs(array $filters): array;

    /**
     * Returns locations associated with given address.
     *
     * @param Address $address
     *
     * @return Collection
     */
    public function getAddressLocations(Address $address): Collection;
}
