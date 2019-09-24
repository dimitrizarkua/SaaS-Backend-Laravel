<?php

namespace App\Components\Auth\Grants;

use League\OAuth2\Server\Grant\PasswordGrant;

/**
 * Class PasswordMobileGrant
 *
 * @package App\Components\Auth
 */
class PasswordMobileGrant extends PasswordGrant
{
    public function getIdentifier()
    {
        return 'password_mobile';
    }
}
