<?php

namespace App\Components\Office365\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class GraphClient
 *
 * @method \App\Components\Office365\Models\UserResource getUser(string $accessToken)
 *
 * @package App\Components\Office365\Facades
 * @see     \App\Components\Office365\GraphClient
 */
class GraphClient extends Facade
{
    public const FACADE_ID = 'graph-client';

    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return self::FACADE_ID;
    }
}
