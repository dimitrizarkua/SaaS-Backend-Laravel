<?php

namespace App\Components\Messages\ServiceProviders\Mailgun;

use App\Core\JsonModel;

/**
 * Class MailgunEvent
 *
 * @package App\Components\Messages\ServiceProviders\Mailgun
 */
class MailgunEvent extends JsonModel
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     *
     * @see \App\Components\Messages\ServiceProviders\Mailgun\MailgunEventTypes
     */
    public $event;

    /**
     * @var float
     */
    public $timestamp;

    /**
     * @var string|null
     */
    public $severity;

    /**
     * @var string|null
     */
    public $reason;

    /**
     * @var \App\Components\Messages\ServiceProviders\Mailgun\MailgunMessage|null
     */
    public $message;

    /**
     * @var array|null
     */
    public $user_variables;

    /**
     * @var \App\Components\Messages\ServiceProviders\Mailgun\MailgunDeliveryStatus|null
     */
    public $delivery_status;
}
