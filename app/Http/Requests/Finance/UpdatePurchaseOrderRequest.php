<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdatePurchaseOrderRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     @OA\Property(
 *        property="recipient_contact_id",
 *        description="Identifier of recipient contact",
 *        type="integer",
 *        example="1"
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
 *        property="job_id",
 *        description="Identifier of job",
 *        type="integer",
 *        nullable=true,
 *        example="1"
 *     ),
 *     @OA\Property(
 *        property="date",
 *        description="Date",
 *        type="string",
 *        format="date",
 *        example="2018-11-10"
 *     ),
 *     @OA\Property(
 *         property="reference",
 *         description="Reference",
 *         type="string",
 *         example="Some reference",
 *         nullable=true
 *     ),
 * )
 */
class UpdatePurchaseOrderRequest extends ApiRequest
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
            'job_id'               => 'nullable|integer|exists:jobs,id',
            'date'                 => 'date',
            'reference'            => 'nullable|string',
        ];
    }
}
