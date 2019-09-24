<?php

namespace App\Components\Messages\Services;

use App\Components\Messages\Enums\MessageParticipantTypes;
use App\Components\Messages\Enums\MessageStatuses;
use App\Components\Messages\Exceptions\NotAllowedException;
use App\Components\Messages\Exceptions\ValidationException;
use App\Components\Messages\Interfaces\MessagingServiceInterface;
use App\Components\Messages\Models\Message;
use App\Components\Messages\Models\MessageData;
use App\Jobs\Messages\DeliverMessage;
use Illuminate\Support\Facades\DB;

/**
 * Class MessagingService
 *
 * @package App\Components\Messages\Services
 */
class MessagingService implements MessagingServiceInterface
{
    /**
     * Helper method that adds recipients of specific type to result set.
     *
     * @param array      $result
     * @param string     $type Recipient type.
     * @param array|null $recipients
     */
    private function addRecipients(array &$result, string $type, array $recipients = null): void
    {
        if (empty($recipients)) {
            return;
        }

        /** @var \App\Components\Messages\Models\MessageParticipantData $recipient */
        foreach ($recipients as $recipient) {
            $result[] = [
                'type'    => $type,
                'address' => $recipient->getAddress(),
                'name'    => $recipient->getName(),
            ];
        }
    }

    /**
     * Helper method that creates array with message recipients to be saved to database.
     *
     * @param \App\Components\Messages\Models\MessageData $data Message data.
     *
     * @return array
     */
    private function makeRecipientsData(MessageData $data): array
    {
        $result = [];

        $this->addRecipients($result, MessageParticipantTypes::TO, $data->getTo());
        $this->addRecipients($result, MessageParticipantTypes::CC, $data->getCc());
        $this->addRecipients($result, MessageParticipantTypes::BCC, $data->getBcc());

        return $result;
    }

    /**
     * Helper method that saves new message to db.
     *
     * @param \App\Components\Messages\Models\MessageData $data       Message data.
     * @param bool                                        $isIncoming Defines whether this message is incoming or
     *                                                                outgoing.
     *
     * @return \App\Components\Messages\Models\Message
     * @throws \Throwable
     */
    private function saveNewMessage(MessageData $data, bool $isIncoming = false): Message
    {
        /** @var \App\Components\Messages\Models\Message $message */

        $message = null;

        DB::transaction(function () use (&$message, $data, $isIncoming) {
            // Save message

            $message = new Message();
            $message->guard(['*' => false]);
            $message->fill([
                'is_incoming'                => $isIncoming,
                'sender_user_id'             => $isIncoming ? null : $data->getSenderId(),
                'external_system_message_id' => $isIncoming ? $data->getExternalMessageId() : null,
                'message_type'               => $data->getType(),
                'from_address'               => $data->getFrom() ? $data->getFrom()->getAddress() : null,
                'from_name'                  => $data->getFrom() ? $data->getFrom()->getName() : null,
                'subject'                    => $data->getSubject(),
                'message_body'               => $data->getBody(),
            ]);
            $message->saveOrFail();

            // Add status

            $status = $message->statuses()->create([
                'status' => $isIncoming ? MessageStatuses::RECEIVED : MessageStatuses::DRAFT,
            ]);
            $message->statuses()->save($status);

            // Add recipients

            $recipients = $message->recipients()->createMany($this->makeRecipientsData($data));
            $message->recipients()->saveMany($recipients);

            // Add attachments

            $attachments = $data->getAttachments();
            if (!empty($attachments)) {
                $message->documents()->attach($attachments);
            }

            $message->resolveMentions();
        });

        return $message;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Messages\Exceptions\ValidationException
     * @throws \Throwable
     */
    public function storeIncomingMessage(MessageData $data): Message
    {
        if (!$data->validate()) {
            throw new ValidationException('Unable to create message. Some of the fields has errors.');
        }

        if (!$data->getFrom()) {
            throw new NotAllowedException('Incoming messages couldn\'t be stored without sender information');
        }

        return $this->saveNewMessage($data, true);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Messages\Exceptions\ValidationException
     * @throws \Throwable
     */
    public function createOutgoingMessage(MessageData $data, bool $sendImmediately = false): Message
    {
        if (!$data->validate()) {
            throw new ValidationException('Unable to create message. Some of the fields has errors.');
        }

        $message = $this->saveNewMessage($data, false);

        if ($sendImmediately) {
            $this->send($message->id);
        }

        return $message;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getMessage(int $messageId): Message
    {
        return Message::findOrFail($messageId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Messages\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function updateOutgoingMessage(int $messageId, MessageData $data, bool $sendImmediately = false): Message
    {
        if (!$data->validate()) {
            throw new ValidationException('Unable to update message. Some of the fields has errors.');
        }

        $message = $this->getMessage($messageId);

        if (!$message->canBeEdited()) {
            throw new NotAllowedException('Only draft messages can be edited.');
        }

        DB::transaction(function () use ($message, $data) {
            // Update message

            $message->guard(['*' => false]);
            $message->fill([
                'is_incoming'    => false,
                'sender_user_id' => $data->getSenderId(),
                'message_type'   => $data->getType(),
                'from_address'   => $data->getFrom() ? $data->getFrom()->getAddress() : null,
                'from_name'      => $data->getFrom() ? $data->getFrom()->getName() : null,
                'subject'        => $data->getSubject(),
                'message_body'   => $data->getBody(),
            ]);
            $message->saveOrFail();

            // Sync recipients

            $message->recipients()->delete();

            $recipients = $message->recipients()->createMany($this->makeRecipientsData($data));
            $message->recipients()->saveMany($recipients);

            // Sync attachments

            $attachments = $data->getAttachments();
            if (!empty($attachments)) {
                $message->documents()->sync($attachments);
            } else {
                $message->documents()->detach();
            }

            $message->resolveMentions();
        });

        if ($sendImmediately) {
            $this->send($message->id);
        }

        return $message;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Messages\Exceptions\NotAllowedException
     *
     * @throws \Throwable
     */
    public function attachDocumentToMessage(int $messageId, int $documentId): Message
    {
        $message = $this->getMessage($messageId);

        if (!$message->canBeEdited()) {
            throw new NotAllowedException('Only draft messages can be edited.');
        }

        try {
            $message->documents()->attach($documentId);
        } catch (\Exception $exception) {
            throw new NotAllowedException('This document has been already attached to this message.');
        }

        return $message;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Messages\Exceptions\NotAllowedException
     *
     * @throws \Throwable
     */
    public function detachDocumentFromMessage(int $messageId, int $documentId): Message
    {
        $message = $this->getMessage($messageId);

        if (!$message->canBeEdited()) {
            throw new NotAllowedException('Only draft messages can be edited.');
        }

        $message->documents()->detach($documentId);

        return $message;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Messages\Exceptions\NotAllowedException
     * @throws \Exception
     */
    public function deleteMessage(int $messageId): void
    {
        $message = $this->getMessage($messageId);

        if (!$message->canBeDeleted()) {
            throw new NotAllowedException('Only draft messages can be deleted.');
        }

        $message->delete();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getMessageStatus(int $messageId): string
    {
        $message = $this->getMessage($messageId);

        return $message->getCurrentStatus();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function send(int $messageId): void
    {
        $message = $this->getMessage($messageId);

        if (!$message->canBeSent()) {
            throw new NotAllowedException('Only draft & failed to deliver messages can be forwarded for delivery.');
        }

        $message->changeStatus(MessageStatuses::READY_FOR_DELIVERY);

        DeliverMessage::dispatch($message)->onQueue('messages');
    }
}
