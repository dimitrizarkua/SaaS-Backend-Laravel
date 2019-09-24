<?php

namespace App\Components\Addresses;

use App\Components\Addresses\Interfaces\AddressServiceInterface;
use App\Components\Addresses\Services\AddressService;
use Illuminate\Support\ServiceProvider;

/**
 * Class AddressesServiceProvider
 *
 * @package App\Components\Addresses
 */
class AddressesServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        AddressServiceInterface::class => AddressService::class,
    ];
}
