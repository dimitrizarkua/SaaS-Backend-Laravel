<?php

namespace App\Components\Addresses\Interfaces;

use App\Components\Addresses\Models\Address;

/**
 * Interface HasAddressesInterface
 *
 * @package App\Components\Addresses\Interfaces
 */
interface HasAddressesInterface
{
    /**
     * Define relationship with addresses table.
     *
     * @param Address $address Address entity to add.
     */
    public function addAddress(Address $address): void;

    /**
     * Returns linked address entity.
     *
     * @return \App\Components\Addresses\Models\Address
     */
    public function getAddress(): Address;
}
