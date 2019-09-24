<?php

namespace App\Components\Teams;

use App\Components\Teams\Interfaces\TeamsServiceInterface;
use App\Components\Teams\Services\TeamsService;
use Illuminate\Support\ServiceProvider;

/**
 * Class TeamsServiceProvider
 *
 * @package App\Components\Teams
 */
class TeamsServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        TeamsServiceInterface::class => TeamsService::class,
    ];
}
