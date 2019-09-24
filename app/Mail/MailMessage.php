<?php

namespace App\Mail;

use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Components\Messages\Enums\MessageParticipantTypes;
use App\Components\Messages\Models\Message;
use App\Components\Messages\Models\MessageParticipantData;
use App\Components\Messages\Models\MessageRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

/**
 * Class MailMessage
 *
 * @package App\Mail
 */
class MailMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var \App\Components\Messages\Models\Message */
    private $message;

    /**
     * Create a new mail message instance.
     *
     * @param Message $message
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Infers and returns sender for the message from the information available in the message.
     *
     * @return array
     */
    private function getSender(): MessageParticipantData
    {
        return new MessageParticipantData(
            config('mail.messages.address'),
            config('mail.messages.name')
        );
    }

    /**
     * Returns array with recipients of specific type.
     *
     * @param string                                   $recipientsType Recipients type.
     * @param \Illuminate\Database\Eloquent\Collection $recipients     Collection of message recipients.
     *
     * @return array
     */
    private function getRecipients(string $recipientsType, Collection $recipients): ?array
    {
        $filtered = $recipients->filter(function (MessageRecipient $recipient) use ($recipientsType) {
            return $recipientsType === $recipient->type;
        });

        return $filtered->isEmpty() ? null : $filtered->all();
    }

    /**
     * Adds recipients to the current instance.
     */
    private function addRecipients(): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection $recipients */
        $recipients = $this->message->recipients;

        $to = $this->getRecipients(MessageParticipantTypes::TO, $recipients);

        /** @var \App\Components\Messages\Models\MessageRecipient $recipient */
        foreach ($to as $recipient) {
            $this->to($recipient->address, $recipient->name);
        }

        $cc = $this->getRecipients(MessageParticipantTypes::CC, $recipients);
        if (!empty($cc)) {
            foreach ($cc as $recipient) {
                $this->cc($recipient->address, $recipient->name);
            }
        }

        $bcc = $this->getRecipients(MessageParticipantTypes::BCC, $recipients);
        if (!empty($bcc)) {
            foreach ($bcc as $recipient) {
                $this->bcc($recipient->address, $recipient->name);
            }
        }
    }

    /**
     * Adds attachments to the current instance.
     */
    private function addAttachments(): void
    {
        if (empty($this->message->documents)) {
            return;
        }

        /** @var \App\Components\Documents\Interfaces\DocumentsServiceInterface $documentService */
        $documentService = App::make(DocumentsServiceInterface::class);

        foreach ($this->message->documents as $document) {
            $this->attachFromStorageDisk(
                $documentService->getDiskName(),
                $document->storage_uid,
                $document->file_name,
                null !== $document->mime_type ? ['mime' => $document->mime_type] : null
            );
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $sender = $this->getSender();

        $this
            ->from($sender->getAddress(), $sender->getName())
            ->replyTo(config('mail.messages.address'), config('mail.messages.name'))
            ->subject($this->message->subject ?? 'Message from Steamatic')
            ->view('messages.generic', ['message_content' => $this->message->message_body_resolved]);

        $this->addRecipients();
        $this->addAttachments();

        return $this;
    }
}
