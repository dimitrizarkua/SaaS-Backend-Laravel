<?php

namespace App\Components\Users;

use App\Components\Users\Interfaces\UserProfileServiceInterface;
use App\Components\Users\Services\UserProfileService;
use Illuminate\Support\ServiceProvider;

/**
 * Class UsersServiceProvider
 *
 * @package App\Components\Users
 */
class UsersServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        UserProfileServiceInterface::class => UserProfileService::class,
    ];
}
