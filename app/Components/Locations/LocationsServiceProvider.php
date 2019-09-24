<?php

namespace App\Components\Locations;

use App\Components\Locations\Interfaces\LocationsServiceInterface;
use App\Components\Locations\Services\LocationsService;
use Illuminate\Support\ServiceProvider;

/**
 * Class LocationsServiceProvider
 *
 * @package App\Components\Locations
 */
class LocationsServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        LocationsServiceInterface::class => LocationsService::class,
    ];
}
