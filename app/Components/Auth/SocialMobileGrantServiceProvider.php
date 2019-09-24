<?php

namespace App\Components\Auth;

use Adaojunior\Passport\SocialUserResolverInterface;
use App\Components\Auth\Grants\SocialMobileGrant;
use DateInterval;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use League\OAuth2\Server\AuthorizationServer;

/**
 * Class SocialMobileGrantServiceProvider
 *
 * @package App\Components\Auth
 */
class SocialMobileGrantServiceProvider extends ServiceProvider
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
        $grant = new SocialMobileGrant(
            $this->app->make(SocialUserResolverInterface::class),
            $this->app->make(RefreshTokenRepository::class)
        );

        $grant->setRefreshTokenTTL(new DateInterval('P1Y'));

        return $grant;
    }
}
