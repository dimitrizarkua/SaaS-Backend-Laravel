<?php

namespace App\Components\Messages\ServiceProviders\Mailgun;

use App\Core\JsonModel;

/**
 * Class MailgunMessage
 *
 * @package App\Components\Messages\ServiceProviders\Mailgun
 */
class MailgunMessage extends JsonModel
{
    /**
     * @var \App\Components\Messages\ServiceProviders\Mailgun\MailgunMessageHeaders|null
     */
    public $headers;
}
