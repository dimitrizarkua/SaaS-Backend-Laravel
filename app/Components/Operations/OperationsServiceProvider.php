<?php

namespace App\Components\Operations;

use App\Components\Operations\Interfaces\RunsServiceInterface;
use App\Components\Operations\Interfaces\RunTemplateRunsServiceInterface;
use App\Components\Operations\Interfaces\RunTemplatesServiceInterface;
use App\Components\Operations\Interfaces\StaffServiceInterface;
use App\Components\Operations\Interfaces\VehiclesServiceInterface;
use App\Components\Operations\Services\RunsService;
use App\Components\Operations\Services\RunTemplateRunsService;
use App\Components\Operations\Services\RunTemplatesService;
use App\Components\Operations\Services\StaffService;
use App\Components\Operations\Services\VehiclesService;
use Illuminate\Support\ServiceProvider;

/**
 * Class OperationsServiceProvider
 *
 * @package App\Components\Operations
 */
class OperationsServiceProvider extends ServiceProvider
{
    public $bindings = [
        VehiclesServiceInterface::class        => VehiclesService::class,
        RunsServiceInterface::class            => RunsService::class,
        RunTemplatesServiceInterface::class    => RunTemplatesService::class,
        RunTemplateRunsServiceInterface::class => RunTemplateRunsService::class,
        StaffServiceInterface::class           => StaffService::class,
    ];
}
