<?php

namespace App\Http\Requests\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class AttachNoteToJobRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="new_status",
 *          description="Allows to change job status to a new.",
 *          type="string",
 *          enum={"New","On-Hold","In-Progress","Closed","Canceled"},
 *     ),
 * )
 *
 * @package App\Http\Requests\Jobs
 */
class AttachNoteToJobRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'new_status' => ['string', Rule::in(JobStatuses::values())],
        ];
    }
}
