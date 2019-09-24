<?php

namespace App\Components\Messages\ServiceProviders;

use App\Components\Messages\Enums\MessageStatuses;
use App\Components\Messages\Interfaces\MessagingServiceInterface;
use App\Components\Messages\Models\Message;
use App\Components\Messages\ServiceProviders\Mailgun\MailgunEventSeverity;
use App\Components\Messages\ServiceProviders\Mailgun\MailgunEventTypes;
use App\Components\Messages\ServiceProviders\Mailgun\MailgunEventWebhookPayload;
use App\Mail\MailMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Class EmailMessageServiceProvider
 *
 * @package App\Components\Messages\Services
 */
class EmailMessageServiceProvider extends MessageServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function deliver(Message $message): void
    {
        Mail::send(new MailMessage($message), [], function ($mail) use ($message) {
            /** @var $mail \Illuminate\Mail\Message */

            $headers = $mail->getSwiftMessage()->getHeaders();

            $variables = ['steamatic_message_id' => $message->id];

            $headers->addTextHeader('X-Mailgun-Variables', json_encode($variables));
            $headers->addTextHeader('X-Steamatic-Message-Id', $message->id);
        });
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function handleMessageStatusUpdate(array $data): void
    {
        // TODO: Add validation that verifies that payload comes from Mailgun
        // https://documentation.mailgun.com/en/latest/user_manual.html#webhooks

        $handled = [
            MailgunEventTypes::DELIVERED,
            MailgunEventTypes::FAILED,
            MailgunEventTypes::REJECTED,
        ];

        $webhook = new MailgunEventWebhookPayload($data);
        if (!in_array($webhook->event_data->event, $handled)) {
            return;
        }

        if (!isset($webhook->event_data->user_variables['steamatic_message_id'])) {
            Log::notice(
                sprintf(
                    'Won\'t process Mailgun webhook [ID: %s] as it doesn\'t contain steamatic message id.',
                    $webhook->event_data->id
                ),
                ['event_id' => $webhook->event_data->id]
            );

            return;
        }

        /** @var MessagingServiceInterface $service */
        $service = app()->make(MessagingServiceInterface::class);

        $messageId = (int)$webhook->event_data->user_variables['steamatic_message_id'];
        $message = $service->getMessage($messageId);

        if (null === $message->external_system_message_id &&
            !empty($webhook->event_data->message->headers->message_id)
        ) {
            $message->external_system_message_id = $webhook->event_data->message->headers->message_id;
            $message->saveOrFail();
        }

        if ($message->getCurrentStatus() !== MessageStatuses::DELIVERY_IN_PROGRESS) {
            return;
        }

        $newStatus = null;
        $reason = null;

        switch ($webhook->event_data->event) {
            case MailgunEventTypes::DELIVERED:
                $newStatus = MessageStatuses::DELIVERED;
                break;

            case MailgunEventTypes::FAILED:
                if (MailgunEventSeverity::TEMPORARY !== $webhook->event_data->severity) {
                    $newStatus = MessageStatuses::DELIVERY_FAILED;

                    $reason = $webhook->event_data->delivery_status->description;
                    if (empty($reason)) {
                        $reason = $webhook->event_data->delivery_status->message;
                    }
                }
                break;

            case MailgunEventTypes::REJECTED:
                $newStatus = MessageStatuses::DELIVERY_FAILED;
                $reason = 'Delivery reject by Mailgun. Please refer to Mailgun logs.';
                break;
        }

        if (null !== $newStatus) {
            $message->changeStatus($newStatus, $reason);

            Log::info(
                sprintf(
                    'Message [ID:%d] status changed to \'%s\' due to event in Mailgin.',
                    $message->id,
                    $newStatus
                ),
                ['message_id' => $message->id]
            );
        }
    }
}
