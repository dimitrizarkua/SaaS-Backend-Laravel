<?php

namespace App\Components\UsageAndActuals;

use App\Components\UsageAndActuals\Interfaces\EquipmentCategoriesInterface;
use App\Components\UsageAndActuals\Interfaces\InsurerContractsInterface;
use App\Components\UsageAndActuals\Services\EquipmentCategoriesService;
use App\Components\UsageAndActuals\Services\InsurerContractsService;
use Illuminate\Support\ServiceProvider;

/**
 * Class UsageAndActualsServiceProvider
 *
 * @package App\Components\UsageAndActuals
 */
class UsageAndActualsServiceProvider extends ServiceProvider
{
    public $bindings = [
        InsurerContractsInterface::class    => InsurerContractsService::class,
        EquipmentCategoriesInterface::class => EquipmentCategoriesService::class,
    ];
}
