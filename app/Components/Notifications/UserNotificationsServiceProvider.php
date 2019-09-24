<?php

namespace App\Components\Notifications;

use App\Components\Notifications\Services\UserNotificationsService;
use App\Components\Notifications\Interfaces\UserNotificationsServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Class UserNotificationsServiceProvider
 *
 * @package App\Components\Notifications
 */
class UserNotificationsServiceProvider extends ServiceProvider
{
    public $bindings = [
        UserNotificationsServiceInterface::class => UserNotificationsService::class,
    ];
}
