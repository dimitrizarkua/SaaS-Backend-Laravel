<?php

namespace App\Components\Messages\ServiceProviders\Mailgun;

use App\Core\JsonModel;

/**
 * Class MailgunIncomingMessageAttachment
 *
 * @package App\Components\Messages\ServiceProviders\Mailgun
 *
 * @see     https://documentation.mailgun.com/en/latest/user_manual.html#routes
 */
class MailgunIncomingMessageAttachment extends JsonModel
{
    /**
     * Indicates the size of the attachment in bytes.
     *
     * @var int
     */
    public $size;

    /**
     * Contains the url where the attachment can be found. This does not support DELETE.
     *
     * @var string
     */
    public $url;

    /**
     * The name of the attachment.
     *
     * @var string
     */
    public $name;

    /**
     * The content type of the attachment.
     *
     * @var string
     */
    public $content_type;
}
