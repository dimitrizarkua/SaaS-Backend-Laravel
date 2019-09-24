<?php

namespace App\Components\Messages\ServiceProviders\Mailgun;

use App\Core\JsonModel;

/**
 * Class MailgunDeliveryStatus
 *
 * @package App\Components\Messages\ServiceProviders\Mailgun
 */
class MailgunDeliveryStatus extends JsonModel
{
    /**
     * @var string|null
     */
    public $message;

    /**
     * @var string|null
     */
    public $description;
}
