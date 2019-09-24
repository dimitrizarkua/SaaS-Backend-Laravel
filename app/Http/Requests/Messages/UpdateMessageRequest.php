<?php

namespace App\Http\Requests\Messages;

use App\Components\Messages\Enums\MessageParticipantTypes;
use App\Components\Messages\Enums\MessageTypes;
use App\Components\Messages\Models\MessageData;
use App\Http\Requests\ApiRequest;
use App\Rules\FirstName;
use Illuminate\Validation\Rule;

/**
 * Class UpdateMessageRequest
 *
 * @package App\Http\Requests\Messages
 *
 * @OA\Schema(
 *     type="object",
 *     required={"type","recipients", "body"},
 *     @OA\Property(
 *          property="type",
 *          description="Message type",
 *          type="string",
 *          enum={"email","sms"}
 *     ),
 *     @OA\Property(
 *          property="recipients",
 *          type="array",
 *          @OA\Items(
 *              type="object",
 *              required={"type","address"},
 *              @OA\Property(
 *                  property="type",
 *                  description="Recipient type.",
 *                  type="string",
 *                  enum={"to","cc","bcc"},
 *              ),
 *              @OA\Property(
 *                  property="address",
 *                  description="Recipient email or phone number.",
 *                  type="string",
 *                  example="john.doe@example.com",
 *              ),
 *              @OA\Property(
 *                  property="name",
 *                  description="Optional recipient name.",
 *                  type="string",
 *                  example="John Doe",
 *              ),
 *          )
 *     ),
 *     @OA\Property(
 *          property="subject",
 *          description="Message subject. Not applicable for sms messages.",
 *          type="string",
 *          example="Subject",
 *     ),
 *     @OA\Property(
 *          property="body",
 *          description="Message body.",
 *          type="string",
 *          example="Body",
 *     )
 * )
 */
class UpdateMessageRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'type'                 => ['required', Rule::in(MessageTypes::values()),],
            'recipients.*.type'    => ['required', Rule::in(MessageParticipantTypes::getRecipientTypeValues())],
            'recipients.*.address' => ['required', 'email'],
            'recipients.*.name'    => ['string', new FirstName()],
            'subject'              => 'string',
            'body'                 => ['required', 'string'],
            'attachments.*'        => ['integer', Rule::exists('documents', 'id')],
        ];
    }

    /**
     * Convenience method that converts request data into message data object.
     *
     * @return \App\Components\Messages\Models\MessageData
     */
    public function toMessageData(): MessageData
    {
        $data = $this->validated();

        $result = new MessageData($data['type']);
        $result
            ->setSubject($data['subject'] ?? null)
            ->setBody($data['body'])
            ->setAttachments($data['attachments'] ?? null);

        foreach ($data['recipients'] as $recipientData) {
            $type = $recipientData['type'];
            $address = $recipientData['address'];
            $name = $recipientData['name'] ?? null;

            if (MessageParticipantTypes::TO === $type) {
                $result->addToRecipient($address, $name);
            } elseif (MessageParticipantTypes::CC === $type) {
                $result->addCcRecipient($address, $name);
            } elseif (MessageParticipantTypes::BCC === $type) {
                $result->addBccRecipient($address, $name);
            }
        }

        return $result;
    }
}
