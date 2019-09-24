<?php

namespace App\Components\Office365\Interfaces;

use App\Models\User;

/**
 * Interface MicrosoftServiceInterface
 *
 * @package App\Components\Office365\Interfaces
 */
interface MicrosoftServiceInterface
{
    /**
     * Tries to retrieve user from Microsoft Graph API and creates new user in the application.
     *
     * @param string $accessToken Access token for the Microsoft Graph API.
     *
     * @throws \Exception
     * @throws \App\Components\Office365\Exceptions\NotAllowedException in case of wrong email.
     *
     * @return \App\Models\User
     */
    public function createOrGetUser(string $accessToken): User;
}
