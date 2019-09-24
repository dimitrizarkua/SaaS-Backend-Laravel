<?php

namespace App\Components\Messages\Interfaces;

use App\Components\Messages\Models\Message;
use App\Components\Messages\Models\MessageData;

/**
 * Interface MessagingServiceInterface
 *
 * @package App\Components\Messages\Interfaces
 */
interface MessagingServiceInterface
{
    /**
     * Stores incoming message in the database.
     *
     * @param \App\Components\Messages\Models\MessageData $data Message data.
     *
     * @return \App\Components\Messages\Models\Message
     */
    public function storeIncomingMessage(MessageData $data): Message;

    /**
     * Creates new outgoing message and (optionally) forwards it for delivery.
     *
     * @param \App\Components\Messages\Models\MessageData $data            Message data.
     * @param bool                                        $sendImmediately Defines whether system should immediately
     *                                                                     forward message for delivery or keep the
     *                                                                     message as a draft.
     *
     * @return \App\Components\Messages\Models\Message
     */
    public function createOutgoingMessage(MessageData $data, bool $sendImmediately = false): Message;

    /**
     * Returns message by id.
     *
     * @param int $messageId Message id.
     *
     * @return \App\Components\Messages\Models\Message
     */
    public function getMessage(int $messageId): Message;

    /**
     * Updates draft outgoing message.
     *
     * @param int                                         $messageId       Message id.
     * @param \App\Components\Messages\Models\MessageData $data            Message data.
     * @param bool                                        $sendImmediately Defines whether system should immediately
     *                                                                     forward message for delivery or keep the
     *                                                                     message as a draft.
     *
     * @return \App\Components\Messages\Models\Message
     */
    public function updateOutgoingMessage(int $messageId, MessageData $data, bool $sendImmediately = false): Message;

    /**
     * Attach document to a draft outgoing message.
     *
     * @param int $messageId  Message id.
     * @param int $documentId Document id.
     *
     * @return \App\Components\Messages\Models\Message
     */
    public function attachDocumentToMessage(int $messageId, int $documentId): Message;

    /**
     * Detach document from a draft outgoing message.
     *
     * @param int $messageId  Message id.
     * @param int $documentId Document id.
     *
     * @return \App\Components\Messages\Models\Message
     */
    public function detachDocumentFromMessage(int $messageId, int $documentId): Message;

    /**
     * Deletes draft outgoing message.
     *
     * @param int $messageId Message id.
     */
    public function deleteMessage(int $messageId): void;

    /**
     * Convenience method that returns current message status by its id.
     *
     * @param int $messageId Message id.
     *
     * @return string
     */
    public function getMessageStatus(int $messageId): string;

    /**
     * Forwards message for delivery. Once message sent for delivery, it couldn't be edited.
     *
     * @param int $messageId Message id.
     */
    public function send(int $messageId): void;
}
