<?php

namespace App\Components\Office365\Services;

use App\Components\Office365\Events\Office365UserCreated;
use App\Components\Office365\Facades\GraphClient;
use App\Components\Office365\Interfaces\MicrosoftServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\App;
use League\OAuth2\Server\Exception\OAuthServerException;

/**
 * Class MicrosoftService
 *
 * @package App\Components\Office365
 */
class MicrosoftService implements MicrosoftServiceInterface
{
    /**
     * Tries to retrieve user from Microsoft Graph API and creates(or retrieve existing) new user in the application.
     *
     * @param string $accessToken Access token for the Microsoft Graph API.
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \League\OAuth2\Server\Exception\OAuthServerException in case of wrong email.
     * @throws \League\OAuth2\Server\Exception\OAuthServerException if there are some network errors.
     *
     * @return \App\Models\User
     */
    public function createOrGetUser(string $accessToken): User
    {
        try {
            $graphUser = GraphClient::getUser($accessToken);
        } catch (\Exception $e) {
            throw new OAuthServerException(
                'Unable to retrieve user data from provider',
                405,
                'invalid_client',
                405
            );
        }

        $allowedDomains = $this->getAllowedDomains();
        $domain         = $graphUser->getEmailDomain();

        if (!in_array($domain, $allowedDomains)) {
            throw new OAuthServerException(
                'Email address belongs to forbidden domain',
                405,
                'invalid_client',
                405
            );
        }

        $user = User::query()
            ->where('azure_graph_id', $graphUser->id)
            ->orWhere('email', $graphUser->getEmail())
            ->first();

        if (null !== $user) {
            if ($user->azure_graph_id === $graphUser->id) {
                return $user;
            }
            if (null === $user->azure_graph_id) {
                $user->azure_graph_id = $graphUser->id;
                $user->saveOrFail();

                return $user;
            }
        }

        $user = User::create([
            'email'          => $graphUser->getEmail(),
            'first_name'     => $graphUser->getFirstName(),
            'last_name'      => $graphUser->getLastName(),
            'azure_graph_id' => $graphUser->id,
        ]);

        event(new Office365UserCreated($user));

        return $user;
    }

    /**
     * Returns list of allowed email domains.
     *
     * @return array
     */
    private function getAllowedDomains(): array
    {
        $allowedDomains = ['steamatic.com.au'];
        if (!App::environment('production')) {
            $allowedDomains[] = 'quantumsoft.ru';
            $allowedDomains[] = 'gmail.com';
        }

        return $allowedDomains;
    }
}
