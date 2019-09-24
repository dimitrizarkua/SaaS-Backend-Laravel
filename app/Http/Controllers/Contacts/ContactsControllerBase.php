<?php

namespace App\Http\Controllers\Contacts;

use App\Components\Contacts\Interfaces\ContactsServiceInterface;
use App\Http\Controllers\Controller;

/**
 * Class ContactsControllerBase
 *
 * @package App\Http\Controllers\Contacts
 */
abstract class ContactsControllerBase extends Controller
{
    /**
     * @var \App\Components\Contacts\Interfaces\ContactsServiceInterface
     */
    protected $service;

    /**
     * ContactsController constructor.
     *
     * @param \App\Components\Contacts\Interfaces\ContactsServiceInterface $contactsService
     */
    public function __construct(ContactsServiceInterface $contactsService)
    {
        $this->service = $contactsService;
    }
}
