<?php

namespace App\Components\Auth;

use App\Components\Auth\Grants\PasswordMobileGrant;
use DateInterval;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use Laravel\Passport\Bridge\UserRepository;
use Laravel\Passport\Bridge\RefreshTokenRepository;

/**
 * Class PasswordMobileGrantServiceProvider
 *
 * @package App\Components\Auth
 */
class PasswordMobileGrantServiceProvider extends ServiceProvider
{
    public function register()
    {
        app()->afterResolving(AuthorizationServer::class, function (AuthorizationServer $server) {
            $grant = $this->makeGrant();
            $server->enableGrantType($grant, new DateInterval('P1Y'));
        });
    }

    protected function makeGrant()
    {
        $grant = new PasswordMobileGrant(
            $this->app->make(UserRepository::class),
            $this->app->make(RefreshTokenRepository::class)
        );

        $grant->setRefreshTokenTTL(new DateInterval('P1Y'));

        return $grant;
    }
}
