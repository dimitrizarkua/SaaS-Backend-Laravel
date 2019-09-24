<?php

namespace App\Components\Auth\Grants;

use Adaojunior\Passport\SocialGrant;

/**
 * Class SocialMobileGrant
 *
 * @package App\Components\Auth
 */
class SocialMobileGrant extends SocialGrant
{
    public function getIdentifier()
    {
        return 'social_mobile';
    }
}
