<?php

namespace App\Components\Messages\ServiceProviders\Mailgun;

use App\Core\JsonModel;

/**
 * Class MailgunEventWebhookPayload
 *
 * @package App\Components\Messages\ServiceProviders\Mailgun
 *
 * @see     https://documentation.mailgun.com/en/latest/user_manual.html#webhooks
 */
class MailgunEventWebhookPayload extends JsonModel
{
    /**
     * @var \App\Components\Messages\ServiceProviders\Mailgun\MailgunSignature
     */
    public $signature;

    /**
     * @var \App\Components\Messages\ServiceProviders\Mailgun\MailgunEvent
     */
    public $event_data;
}
