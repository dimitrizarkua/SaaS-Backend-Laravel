<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateJobServiceRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     required={"name"},
 *     @OA\Property(
 *         property="name",
 *         description="Service name",
 *         type="string",
 *         example="name"
 *     ),
 * )
 */
class CreateJobServiceRequest extends ApiRequest
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
            'name' => 'required|string|unique:job_services',
        ];
    }
}
