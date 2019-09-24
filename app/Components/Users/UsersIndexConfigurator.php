<?php

namespace App\Components\Users;

use App\DefaultIndexConfigurator;

/**
 * Class UsersIndexConfigurator
 *
 * @package App\Components\Users
 */
class UsersIndexConfigurator extends DefaultIndexConfigurator
{
    /**
     * Name of the index.
     *
     * @var string
     */
    protected $name = 'users_index';
}
