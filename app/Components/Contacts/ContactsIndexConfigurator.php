<?php

namespace App\Components\Contacts;

use App\DefaultIndexConfigurator;
use ScoutElastic\Migratable;

/**
 * Class ContactsIndexConfigurator
 *
 * @package App\Components\Contacts
 */
class ContactsIndexConfigurator extends DefaultIndexConfigurator
{
    use Migratable;
}
