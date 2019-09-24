<?php

namespace App\Components\Messages\Interfaces;

use App\Components\Messages\Models\Message;

/**
 * Interface MessageDeliveryServiceInterface
 *
 * @package App\Components\Messages\Interfaces
 */
interface MessageDeliveryServiceInterface
{
    /**
     * Attempts to deliver a message.
     *
     * @param \App\Components\Messages\Models\Message $message Message.
     */
    public function deliver(Message $message): void;

    /**
     * Handles message status update from external system.
     *
     * @param string $messageType Message type.
     * @param array  $data        Status update data from provider.
     */
    public function handleMessageStatusUpdateFromProvider(string $messageType, array $data): void;
}
