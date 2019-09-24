<?php

namespace App\Components\Office365;

use App\Components\Office365\Facades\GraphClient as GraphClientFacade;
use App\Components\Office365\Interfaces\MicrosoftServiceInterface;
use App\Components\Office365\Services\MicrosoftService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

/**
 * Class Office365ServiceProvider
 *
 * @package App\Components\Office365
 */
class Office365ServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        MicrosoftServiceInterface::class => MicrosoftService::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        App::bind(GraphClientFacade::FACADE_ID, function () {
            return new GraphClient();
        });
    }
}
