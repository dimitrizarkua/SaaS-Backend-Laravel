<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use App\Rules\BelongsToLocation;
use OpenApi\Annotations as OA;

/**
 * Class CreateCreditNoteRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={"location_id","recipient_contact_id","date"},
 *     @OA\Property(
 *         property="location_id",
 *         description="Identifier of location",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="recipient_contact_id",
 *         description="Identifier of recipient's contact",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *        property="recipient_address",
 *        description="Recipient address",
 *        type="string",
 *        example="300 Collins Street, Brisbane QLD 8000",
 *     ),
 *     @OA\Property(
 *        property="recipient_name",
 *        description="Recipient name",
 *        type="string",
 *        example="Joshua Brown",
 *     ),
 *     @OA\Property(
 *         property="date",
 *         description="Execution start date",
 *         type="string",
 *         format="date",
 *         example="2018-11-10"
 *     ),
 *     @OA\Property(
 *         property="job_id",
 *         description="Identifier of job",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="payment_id",
 *         description="Identifier of payment",
 *         type="integer",
 *         example=1
 *     ),
 * )
 */
class CreateCreditNoteRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @see https://laravel.com/docs/5.7/validation#available-validation-rules
     *
     * @return array
     */
    public function rules(): array
    {
        $ruleSet = [
            'location_id'          => [
                'required',
                'integer',
                'exists:locations,id',
                new BelongsToLocation($this->user()),
            ],
            'recipient_contact_id' => 'required|integer|exists:contacts,id',
            'recipient_address'    => 'string',
            'recipient_name'       => 'string',
            'date'                 => 'required|date_format:Y-m-d',
            'job_id'               => 'integer|exists:jobs,id|nullable',
            'payment_id'           => 'integer|exists:payments,id|nullable',
        ];

        return array_merge(
            $ruleSet,
            arrayMapKeys($this->creditNoteItemRules(), function ($key) {
                return 'items.*.' . $key;
            })
        );
    }

    /**
     * @return array
     */
    private function creditNoteItemRules(): array
    {
        $validator = new CreateCreditNoteItemRequest();

        return $validator->rules();
    }
}
