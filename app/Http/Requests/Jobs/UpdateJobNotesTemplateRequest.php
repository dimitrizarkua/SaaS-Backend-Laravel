<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateJobNotesTemplateRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="name",
 *         description="Template name",
 *         type="string",
 *         example="Job Scheduled"
 *     ),
 *     @OA\Property(
 *         property="body",
 *         description="Template body",
 *         type="string",
 *         example="Some text"
 *     ),
 *     @OA\Property(
 *         property="active",
 *         description="Indicates if the template is active",
 *         type="boolean",
 *         example="true"
 *     ),
 * )
 */
class UpdateJobNotesTemplateRequest extends ApiRequest
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
            'name'   => 'string|unique:job_notes_templates',
            'body'   => 'string',
            'active' => 'boolean',
        ];
    }
}
