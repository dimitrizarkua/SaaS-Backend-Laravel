<?php

namespace App\Components\Messages\ServiceProviders;

use App\Components\Messages\Enums\MessageTypes;

/**
 * Class MessageServiceProviderFactory
 *
 * @package App\Components\Messages\ServiceProviders
 */
class MessageServiceProviderFactory
{
    /**
     * Creates and returns service provider instance for specific message type.
     *
     * @param string $type Service provider type:
     *
     * @return \App\Components\Messages\ServiceProviders\MessageServiceProvider
     *
     * @throws \InvalidArgumentException
     *
     * @see \App\Components\Messages\Enums\MessageTypes
     */
    public static function create(string $type): MessageServiceProvider
    {
        if (MessageTypes::EMAIL === $type) {
            return new EmailMessageServiceProvider();
        }

        throw new \InvalidArgumentException(sprintf(
            'There is no service provider implementation for type %s.',
            $type
        ));
    }
}
