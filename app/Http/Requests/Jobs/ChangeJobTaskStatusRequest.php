<?php

namespace App\Http\Requests\Jobs;

use App\Components\Jobs\Enums\JobTaskStatuses;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class ChangeJobTaskStatusRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"status"},
 *     @OA\Property(
 *          property="status",
 *          description="New task status",
 *          allOf={@OA\Schema(ref="#/components/schemas/JobTaskStatuses")}
 *     )
 * )
 *
 * @package App\Http\Requests\Jobs
 */
class ChangeJobTaskStatusRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(JobTaskStatuses::values())],
        ];
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->get('status');
    }
}
