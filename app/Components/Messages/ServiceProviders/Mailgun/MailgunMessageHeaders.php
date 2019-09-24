<?php

namespace App\Components\Messages\ServiceProviders\Mailgun;

use App\Core\JsonModel;

/**
 * Class MailgunMessageHeaders
 *
 * @package App\Components\Messages\ServiceProviders\Mailgun
 */
class MailgunMessageHeaders extends JsonModel
{
    /**
     * @var string|null
     */
    public $message_id;
}
