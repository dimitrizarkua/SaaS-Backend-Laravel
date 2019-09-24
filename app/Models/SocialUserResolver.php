<?php

namespace App\Models;

use Adaojunior\Passport\SocialGrantException;
use Adaojunior\Passport\SocialUserResolverInterface;
use App\Components\Office365\Services\MicrosoftService;
use Laravel\Passport\Passport;

/**
 * Class SocialUserResolver
 *
 * @OA\Schema (required={"id","email","created_at","updated_at"})
 */
class SocialUserResolver implements SocialUserResolverInterface
{

    /**
     * Resolves user by given network and access token.
     *
     * @param string $network
     * @param string $accessToken
     *
     * @throws \Exception
     * @throws \Throwable
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function resolve($network, $accessToken, $accessTokenSecret = null)
    {
        switch ($network) {
            case 'office365':
                Passport::tokensExpireIn(now()->addMinutes(60));

                return $this->authWithOffice365($accessToken);
                break;
            default:
                throw SocialGrantException::invalidNetwork();
                break;
        }
    }

    /**
     * Resolves user by Office365 access token.
     *
     * @param string $accessToken Access token for the Microsoft Graph API.
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \App\Components\Office365\Exceptions\NotAllowedException in case of wrong email.
     * @throws \App\Components\Office365\Exceptions\NotAllowedException if there are some network errors.
     *
     * @return \App\Models\User
     */
    public function authWithOffice365(string $accessToken): User
    {
        return app()->make(MicrosoftService::class)->createOrGetUser($accessToken);
    }
}
