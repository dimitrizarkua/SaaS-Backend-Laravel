<?php

namespace App\Components\Messages\ServiceProviders\Mailgun;

use App\Core\JsonModel;

/**
 * Class MailgunSignature
 *
 * @package App\Components\Messages\ServiceProviders\Mailgun
 */
class MailgunSignature extends JsonModel
{
    /**
     * @var int
     */
    public $timestamp;

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $signature;
}
