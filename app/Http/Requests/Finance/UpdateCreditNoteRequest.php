<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateCreditNoteRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="recipient_contact_id",
 *         description="Recipient contact identifier",
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
 *         description="Job identifier",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="payment_id",
 *         description="Payment identifier",
 *         type="integer",
 *         example=1
 *     ),
 * )
 */
class UpdateCreditNoteRequest extends ApiRequest
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
        return [
            'recipient_contact_id' => 'integer|exists:contacts,id',
            'recipient_address'    => 'string',
            'recipient_name'       => 'string',
            'date'                 => 'date_format:Y-m-d',
            'job_id'               => 'integer|exists:jobs,id|nullable',
            'payment_id'           => 'integer|exists:payments,id|nullable',
        ];
    }
}
