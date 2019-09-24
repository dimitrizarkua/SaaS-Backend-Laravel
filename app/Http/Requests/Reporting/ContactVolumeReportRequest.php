<?php

namespace App\Http\Requests\Reporting;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class ContactVolumeReportRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={"location_id", "date_from", "date_to"},
 *     @OA\Property(
 *          property="location_id",
 *          type="integer",
 *          description="Location identifier.",
 *          example=1
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
 *        property="staff_id",
 *        description="User identifier related to staff.",
 *        type="integer",
 *        example="2"
 *     ),
 *     @OA\Property(
 *        property="contact_id",
 *        description="Contact identifier related to company.",
 *        type="integer",
 *        example="2"
 *     ),
 *     @OA\Property(
 *        property="tag_ids",
 *        description="Tag identifiers related to managed contacts.",
 *        type="array",
 *        @OA\Items(
 *              type="integer",
 *              example=1
 *          ),
 *     )
 * )
 */
class ContactVolumeReportRequest extends ApiRequest
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
            'location_id' => 'required|integer|exists:locations,id',
            'date_from'   => 'required|string|date_format:Y-m-d',
            'date_to'     => 'required|string|date_format:Y-m-d|after:date_from',
            'contact_id'  => 'integer|exists:contacts,id',
            'staff_id'    => 'integer|exists:users,id',
            'tag_ids'     => 'array',
            'tag_ids.*'   => 'integer|exists:tags,id',
        ];
    }
}
