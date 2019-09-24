<?php

namespace App\Http\Requests\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class ChangeJobStatusRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="status",
 *         description="New status of the job",
 *         type="string",
 *         example="On-Hold",
 *         enum={"New","On-Hold","In-Progress","Closed","Cancelled"}
 *     ),
 *     @OA\Property(
 *         property="note",
 *         description="Optional note that describes a reason for a status change",
 *         type="string",
 *         example="Some note",
 *     ),
 * )
 */
class ChangeJobStatusRequest extends ApiRequest
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
            'status' => ['required', 'string', Rule::in(JobStatuses::values())],
            'note'   => 'string',
        ];
    }
}
