<?php

namespace App\Components\Messages;

use App\Components\Messages\Interfaces\MessageDeliveryServiceInterface;
use App\Components\Messages\Interfaces\MessagingServiceInterface;
use App\Components\Messages\Services\MessageDeliveryService;
use App\Components\Messages\Services\MessagingService;
use Illuminate\Support\ServiceProvider;

/**
 * Class MessagingServiceProvider
 *
 * @package App\Components\Messages
 */
class MessagingServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        MessagingServiceInterface::class       => MessagingService::class,
        MessageDeliveryServiceInterface::class => MessageDeliveryService::class,
    ];
}
