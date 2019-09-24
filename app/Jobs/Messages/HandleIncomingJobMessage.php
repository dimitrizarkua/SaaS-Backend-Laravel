<?php

namespace App\Jobs\Messages;

use App\Components\Documents\Exceptions\DownloadFailedException;
use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Components\Messages\Interfaces\MessagingServiceInterface;
use App\Components\Messages\Models\EmailMessage;
use App\Components\Messages\Models\MessageData;
use App\Components\Messages\ServiceProviders\Mailgun\MailgunIncomingMessageWebhookPayload;
use App\Jobs\Jobs\FindOrCreateOrActivateJobForIncomingMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class HandleIncomingJobMessage
 *
 * @package App\Jobs\Messages
 */
class HandleIncomingJobMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    protected $data;

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Create a new job instance.
     *
     * @param array $data Message data from provider.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Creates documents from attachments.
     *
     * @param \App\Components\Documents\Interfaces\DocumentsServiceInterface                         $service
     * @param \App\Components\Messages\ServiceProviders\Mailgun\MailgunIncomingMessageWebhookPayload $payload
     *
     * @return array|null Array with created documents ids or null.
     *
     * @throws \Throwable
     * @throws \App\Components\Documents\Exceptions\DownloadFailedException
     */
    private function createDocumentsFromAttachments(
        DocumentsServiceInterface $service,
        MailgunIncomingMessageWebhookPayload $payload
    ): ?array {
        if (empty($payload->attachments)) {
            return null;
        }

        $credentials = base64_encode('api:' . env('MAILGUN_SECRET'));
        $headers = ['Authorization: Basic ' . $credentials];

        $documentIds = [];

        try {
            foreach ($payload->attachments as $attachment) {
                $document = $service->createDocumentFromUrl(
                    $attachment->url,
                    $attachment->name,
                    $attachment->content_type,
                    $headers
                );
                $documentIds[] = $document->id;
            }
        } catch (DownloadFailedException $exception) {
            foreach ($documentIds as $documentId) {
                $service->deleteDocument($documentId, true);
            }
            throw $exception;
        }

        return $documentIds;
    }

    /**
     * Creates message data from payload.
     *
     * @param \App\Components\Messages\ServiceProviders\Mailgun\MailgunIncomingMessageWebhookPayload $payload
     *
     * @return \App\Components\Messages\Models\MessageData
     */
    private function createMessageData(MailgunIncomingMessageWebhookPayload $payload): MessageData
    {
        $messageData = new EmailMessage();
        $messageData
            ->setSubject($payload->subject)
            ->setBody($payload->stripped_text ?? $payload->body_plain ?? '');

        // Store FROM

        $sender = mailparse_rfc822_parse_addresses($payload->from);
        $messageData->setSender($sender[0]['address'], $sender[0]['display']);

        // Store TO recipients

        $header = $payload->message_headers->firstWhere('name', 'To');
        if ($header) {
            $recipients = mailparse_rfc822_parse_addresses($header['value']);
            foreach ($recipients as $recipient) {
                $messageData->addToRecipient($recipient['address'], $recipient['display']);
            }
        }

        // Store CC recipients if any

        $header = $payload->message_headers->firstWhere('name', 'Cc');
        if ($header) {
            $recipients = mailparse_rfc822_parse_addresses($header['value']);
            foreach ($recipients as $recipient) {
                $messageData->addCcRecipient($recipient['address'], $recipient['display']);
            }
        }

        // Store Mailgun's message id if any

        $header = $payload->message_headers->firstWhere('name', 'Message-Id');
        if ($header) {
            $value = $header['value'];
            $value = substr($value, 1, strlen($value) - 2);

            $messageData->setExternalMessageId($value);
        }

        // Store attachments

        return $messageData;
    }

    /**
     * Execute the job.
     *
     * @param MessagingServiceInterface $messagingService Messaging service.
     * @param DocumentsServiceInterface $documentsService Documents service.
     *
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function handle(MessagingServiceInterface $messagingService, DocumentsServiceInterface $documentsService)
    {
        $payload = new MailgunIncomingMessageWebhookPayload($this->data);

        $messageData = $this->createMessageData($payload);

        $attachmentIds = $this->createDocumentsFromAttachments($documentsService, $payload);
        $messageData->setAttachments($attachmentIds);

        $message = $messagingService->storeIncomingMessage($messageData);

        Log::info(
            sprintf(
                'Incoming message [%s] successfully stored in the system [MESSAGE_ID:%d].',
                $message->external_system_message_id,
                $message->id
            ),
            ['message_id' => $message->id]
        );

        FindOrCreateOrActivateJobForIncomingMessage::dispatch($message->id)->onQueue('jobs');
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addSeconds(5);
    }
}
