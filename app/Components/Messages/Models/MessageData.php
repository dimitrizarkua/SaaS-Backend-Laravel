<?php

namespace App\Components\Messages\Models;

use App\Components\Messages\Enums\MessageTypes;
use App\Components\Messages\Exceptions\ValidationException;
use App\Core\Validatable;
use Illuminate\Validation\Rule;

/**
 * Class MessageData
 *
 * @package App\Components\Messages\Models
 */
class MessageData
{
    use Validatable;

    /**
     * @var string
     */
    private $type;

    /**
     * Sender id (user id). Null for messages sent by the system and incoming messages from customer.
     *
     * @var integer|null
     */
    private $senderId;

    /**
     * Message sender. Allows to explicitly set sender address (and name) if $senderId is undefined
     * or in case when another address should be used.
     *
     * @var \App\Components\Messages\Models\MessageParticipantData|null
     */
    private $from;

    /**
     * Main recipient(s) of the message.
     *
     * @var \App\Components\Messages\Models\MessageParticipantData[]
     */
    private $to;

    /**
     * Carbon copy recipients.
     *
     * @var \App\Components\Messages\Models\MessageParticipantData[]|null
     */
    private $cc;

    /**
     * Blind carbon copy recipients.
     *
     * @var \App\Components\Messages\Models\MessageParticipantData[]|null
     */
    private $bcc;

    /**
     * Message subject. Not required for "phone" (or sms) messages.
     *
     * @var string|null
     */
    private $subject;

    /**
     * Message body.
     *
     * @var string
     */
    private $body;

    /**
     * Attachments (documents ids).
     *
     * @var integer[]|null
     */
    private $attachments;

    /**
     * External messages id. Will be ignored for outgoing messages.
     *
     * @var string|null
     */
    private $externalMessageId;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function setType(string $type): self
    {
        if (!in_array($type, MessageTypes::values())) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid type %s specified, allowed values are: %s',
                $type,
                implode(',', MessageTypes::values())
            ));
        }
        $this->type = $type;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSenderId(): ?int
    {
        return $this->senderId;
    }

    /**
     * @param int|null $senderId
     *
     * @return self
     */
    public function setSenderId(?int $senderId): self
    {
        $this->senderId = $senderId;

        return $this;
    }

    /**
     * @return \App\Components\Messages\Models\MessageParticipantData|null
     */
    public function getFrom(): ?MessageParticipantData
    {
        return $this->from;
    }

    /**
     * @param \App\Components\Messages\Models\MessageParticipantData|null $from
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function setFrom(?MessageParticipantData $from): self
    {
        if (!$from->validate()) {
            throw new \InvalidArgumentException('Invalid data provided.');
        }

        $this->from = $from;

        return $this;
    }

    /**
     * @return \App\Components\Messages\Models\MessageParticipantData[]
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @param \App\Components\Messages\Models\MessageParticipantData[] $to
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     * @throws \App\Components\Messages\Exceptions\ValidationException
     */
    public function setTo(array $to): self
    {
        foreach ($to as $recipient) {
            if (!($recipient instanceof MessageParticipantData)) {
                throw new \InvalidArgumentException('Recipient should be instance of ' . MessageParticipantData::class);
            }

            if (!$recipient->validate()) {
                throw new ValidationException('Some of the recipients\' fields are invalid.');
            }
        }

        $this->to = $to;

        return $this;
    }

    /**
     * @return null|\App\Components\Messages\Models\MessageParticipantData[]
     */
    public function getCc(): ?array
    {
        return $this->cc;
    }

    /**
     * @param null|\App\Components\Messages\Models\MessageParticipantData[] $cc
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     * @throws \App\Components\Messages\Exceptions\ValidationException
     */
    public function setCc(?array $cc): self
    {
        foreach ($cc as $recipient) {
            if (!($recipient instanceof MessageParticipantData)) {
                throw new \InvalidArgumentException('Recipient should be instance of ' . MessageParticipantData::class);
            }

            if (!$recipient->validate()) {
                throw new ValidationException('Some of the recipients\' fields are invalid.');
            }
        }

        $this->cc = $cc;

        return $this;
    }

    /**
     * @return null|\App\Components\Messages\Models\MessageParticipantData[]
     */
    public function getBcc(): ?array
    {
        return $this->bcc;
    }

    /**
     * @param null|\App\Components\Messages\Models\MessageParticipantData[] $bcc
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     * @throws \App\Components\Messages\Exceptions\ValidationException
     */
    public function setBcc(?array $bcc): self
    {
        foreach ($bcc as $recipient) {
            if (!($recipient instanceof MessageParticipantData)) {
                throw new \InvalidArgumentException('Recipient should be instance of ' . MessageParticipantData::class);
            }

            if (!$recipient->validate()) {
                throw new ValidationException('Some of the recipients\' fields are invalid.');
            }
        }

        $this->bcc = $bcc;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param null|string $subject
     *
     * @return self
     */
    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string $body|null
     *
     * @return self
     */
    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return integer[]|null
     */
    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    /**
     * @param integer[]|null $attachments
     *
     * @return self
     */
    public function setAttachments(?array $attachments): self
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getExternalMessageId(): ?string
    {
        return $this->externalMessageId;
    }

    /**
     * @param null|string $externalMessageId
     *
     * @return self
     */
    public function setExternalMessageId(?string $externalMessageId): self
    {
        $this->externalMessageId = $externalMessageId;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function getValidationRules(): array
    {
        return [
            'type'          => ['required', Rule::in(MessageTypes::values()),],
            'subject'       => 'string|nullable',
            'body'          => 'string|nullable',
            'attachments.*' => ['integer', Rule::exists('documents', 'id')],
        ];
    }

    /**
     * MessageData constructor.
     *
     * @param string|null $type     Message type.
     * @param int|null    $senderId Sender id (user id).
     * @param array|null  $to       Message recipients.
     * @param string|null $body     Message body.
     *
     * @see \App\Components\Messages\Enums\MessageTypes
     */
    public function __construct(string $type = null, int $senderId = null, array $to = null, string $body = null)
    {
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $senderId) {
            $this->setSenderId($senderId);
        }
        if (null !== $to) {
            $this->setTo($to);
        }
        if (null !== $body) {
            $this->setBody($body);
        }
    }

    /**
     * Helper method that address recipient to target array.
     *
     * @param \App\Components\Messages\Models\MessageParticipantData $recipient Recipient data.
     * @param array|null                                             $target    Target array.
     */
    protected function addRecipient(MessageParticipantData $recipient, ?array &$target): void
    {
        if (!$target) {
            $target = [];
        }

        $target[] = $recipient;
    }

    /**
     * Convenience method to set message sender.
     *
     * @param string      $address
     * @param null|string $name
     *
     * @return \App\Components\Messages\Models\MessageData
     */
    public function setSender(string $address, ?string $name = null): self
    {
        $sender = new MessageParticipantData($address, $name);

        $this->setFrom($sender);

        return $this;
    }

    /**
     * Convenience method to add recipient of type TO.
     *
     * @param string      $address Recipient address.
     * @param null|string $name    Recipient name.
     *
     * @return self
     */
    public function addToRecipient(string $address, ?string $name = null): self
    {
        $recipient = new MessageParticipantData($address, $name);

        $this->addRecipient($recipient, $this->to);

        return $this;
    }

    /**
     * Convenience method to add recipient of type CC.
     *
     * @param string      $address Recipient address.
     * @param null|string $name    Recipient name.
     *
     * @return self
     */
    public function addCcRecipient(string $address, ?string $name = null): self
    {
        $recipient = new MessageParticipantData($address, $name);

        $this->addRecipient($recipient, $this->cc);

        return $this;
    }

    /**
     * Convenience method to add recipient of type BCC.
     *
     * @param string      $address Recipient address.
     * @param null|string $name    Recipient name.
     *
     * @return self
     */
    public function addBccRecipient(string $address, ?string $name = null): self
    {
        $recipient = new MessageParticipantData($address, $name);

        $this->addRecipient($recipient, $this->bcc);

        return $this;
    }
}
