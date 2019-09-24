<?php

namespace App\Components\Contacts;

use App\Components\Contacts\Interfaces\ContactsServiceInterface;
use App\Components\Contacts\Services\ContactsService;
use Illuminate\Support\ServiceProvider;

/**
 * Class ContactsServiceProvider
 *
 * @package App\Components\Contacts
 */
class ContactsServiceProvider extends ServiceProvider
{
    public $bindings = [
        ContactsServiceInterface::class => ContactsService::class,
    ];
}
