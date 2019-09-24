<?php

namespace App\Components\Messages\ServiceProviders\Mailgun;

use App\Core\JsonModel;

/**
 * Class MailgunIncomingMessageWebhookPayload
 *
 * @package App\Components\Messages\ServiceProviders\Mailgun
 *
 * @see     https://documentation.mailgun.com/en/latest/user_manual.html#routes
 */
class MailgunIncomingMessageWebhookPayload extends JsonModel
{
    /**
     * Number of seconds passed since January 1, 1970 (see securing webhooks).
     *
     * @see https://documentation.mailgun.com/en/latest/user_manual.html#webhooks
     *
     * @var int
     */
    public $timestamp;

    /**
     * Randomly generated string with length 50 (see securing webhooks).
     *
     * @see https://documentation.mailgun.com/en/latest/user_manual.html#webhooks
     *
     * @var string
     */
    public $token;

    /**
     * String with hexadecimal digits generate by HMAC algorithm (see securing webhooks).
     *
     * @see https://documentation.mailgun.com/en/latest/user_manual.html#webhooks
     *
     * @var string
     */
    public $signature;

    /**
     * Mailgun domain.
     *
     * @var string
     */
    public $domain;

    /**
     * Message subject.
     *
     * @var string|null
     */
    public $subject;

    /**
     * Sender of the message as reported by MAIL FROM during SMTP chat.
     * Note: this value may differ from From MIME header.
     *
     * @var string
     */
    public $sender;

    /**
     * Format used: "John Doe <john.doe@example.com>"
     *
     * @var string
     */
    public $from;

    /**
     * Recipient of the message as reported by MAIL TO during SMTP chat.
     *
     * @var string
     */
    public $recipient;

    /**
     * Text version of the email. This field is always present.
     * If the incoming message only has HTML body, Mailgun will create a text representation for you.
     *
     * @var string|null
     */
    public $body_plain;

    /**
     * HTML version of the message, if message was multipart.
     * Note that all parts of the message will be posted, not just text/html.
     * For instance if a message arrives with “foo” part it will be posted as “body-foo”.
     *
     * @var string|null
     */
    public $body_html;

    /**
     * Text version of the message without quoted parts and signature block (if found).
     *
     * @var string|null
     */
    public $stripped_text;

    /**
     * The signature block stripped from the plain text message (if found).
     *
     * @var string|null
     */
    public $stripped_signature;

    /**
     * HTML version of the message, without quoted parts.
     *
     * @var string|null
     */
    public $stripped_html;

    /**
     * List of all MIME headers dumped to a json string (order of headers preserved).
     * Please note that original type of this field is string.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public $message_headers;

    /**
     * Contains a json list of metadata objects, one for each attachment.
     * Please note that original type of this field is string.
     *
     * @var MailgunIncomingMessageAttachment[]|null
     */
    public $attachments;

    /**
     * @param string $value
     */
    public function setAttachments(string $value): void
    {
        if (empty($value)) {
            return;
        }

        $attachments = \GuzzleHttp\json_decode(JsonModel::replaceHyphensWithUnderscores($value), true);

        $this->attachments = MailgunIncomingMessageAttachment::createManyFromJson($attachments);
    }

    /**
     * @param string $value
     */
    public function setMessageHeaders(string $value): void
    {
        if (empty($value)) {
            return;
        }

        $headers = collect(\GuzzleHttp\json_decode($value, true));

        $modified = $headers->map(function ($item) {
            return [
                'name'  => $item[0],
                'value' => $item[1],
            ];
        });

        $this->message_headers = $modified;
    }
}
