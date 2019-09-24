<?php

namespace App\Http\Requests\Reporting;

use App\Http\Requests\ApiRequest;
use App\Rules\BelongsToLocation;
use OpenApi\Annotations as OA;

/**
 * Class IncomeReportRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={"location_id"},
 *     @OA\Property(
 *          property="location_id",
 *          type="integer",
 *          description="Location identifier. Use to only include invoices owned by the specified location.",
 *          example=1
 *     ),
 *     @OA\Property(
 *        property="gl_account_id",
 *        description="GL account identifier",
 *        type="integer",
 *        example="1"
 *     ),
 *     @OA\Property(
 *        property="date_from",
 *        description="Date from",
 *        type="string",
 *        format="date",
 *        example="2018-11-10"
 *     ),
 *     @OA\Property(
 *        property="date_to",
 *        description="Date to",
 *        type="string",
 *        format="date",
 *        example="2018-11-30"
 *     ),
 *     @OA\Property(
 *        property="recipient_contact_id",
 *        description="Contact identifier related to the Invoice To field.",
 *        type="integer",
 *        example="2"
 *     )
 * )
 */
class IncomeReportRequest extends ApiRequest
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
            'location_id'          => [
                'required',
                'integer',
                'exists:locations,id',
                new BelongsToLocation($this->user()),
            ],
            'gl_account_id'        => 'integer|exists:gl_accounts,id',
            'date_from'            => 'string|date_format:Y-m-d',
            'date_to'              => 'string|date_format:Y-m-d',
            'recipient_contact_id' => 'integer|exists:contacts,id',
        ];
    }
}
