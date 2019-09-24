<?php

namespace App\Components\Messages\ServiceProviders;

use App\Components\Messages\Models\Message;

/**
 * Class MessageServiceProvider
 *
 * @package App\Components\Messages\ServiceProviders
 */
abstract class MessageServiceProvider
{
    /**
     * Attempts to deliver a message.
     *
     * @param \App\Components\Messages\Models\Message $message Message.
     */
    abstract public function deliver(Message $message): void;

    /**
     * @param array $data Array with information about status update.
     */
    abstract public function handleMessageStatusUpdate(array $data): void;
}
