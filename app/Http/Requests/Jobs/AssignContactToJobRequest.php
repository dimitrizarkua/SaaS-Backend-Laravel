<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class AssignContactToJobRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"assignment_type_id"},
 *     @OA\Property(
 *          property="assignment_type_id",
 *          description="Assignment type id.",
 *          type="integer",
 *          example=1
 *     ),
 *     @OA\Property(
 *          property="invoice_to",
 *          description="Defines whether specified contact should be invoiced or not.",
 *          type="boolean",
 *          example=true
 *     ),
 * )
 *
 * @package App\Http\Requests\Jobs
 */
class AssignContactToJobRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'assignment_type_id' => [
                'required',
                'integer',
                Rule::exists('job_contact_assignment_types', 'id'),
            ],
            'invoice_to'         => 'boolean',
        ];
    }
}
