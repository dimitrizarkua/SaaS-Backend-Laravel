<?php

namespace App\Components\Messages\Services;

use App\Components\Messages\Interfaces\MessageDeliveryServiceInterface;
use App\Components\Messages\Models\Message;
use App\Components\Messages\ServiceProviders\MessageServiceProviderFactory;

/**
 * Class MessageDeliveryService
 *
 * @package App\Components\Messages\Services
 */
class MessageDeliveryService implements MessageDeliveryServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function deliver(Message $message): void
    {
        $serviceProvider = MessageServiceProviderFactory::create($message->message_type);
        $serviceProvider->deliver($message);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function handleMessageStatusUpdateFromProvider(string $messageType, array $data): void
    {
        $serviceProvider = MessageServiceProviderFactory::create($messageType);
        $serviceProvider->handleMessageStatusUpdate($data);
    }
}
