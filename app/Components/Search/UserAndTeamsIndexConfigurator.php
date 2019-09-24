<?php

namespace App\Components\Search;

use App\DefaultIndexConfigurator;

/**
 * Class UserAndTeamsIndexConfigurator
 *
 * @package App\Components\Search
 */
class UserAndTeamsIndexConfigurator extends DefaultIndexConfigurator
{
    /**
     * Name of the index.
     *
     * @var string
     */
    protected $name = 'users_and_teams';
}
