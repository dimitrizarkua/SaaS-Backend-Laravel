<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateJobTaskTypeRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="name",
 *          description="Job task type name",
 *          type="string",
 *          example="Equipment pickup",
 *     ),
 *     @OA\Property(
 *          property="can_be_scheduled",
 *          type="boolean",
 *          description="Indicates whether a task with this type can be scheduled",
 *          example=true
 *     ),
 *     @OA\Property(
 *          property="default_duration_minutes",
 *          type="integer",
 *          description="Default task duration (in minutes)",
 *          example=120
 *     ),
 *     @OA\Property(
 *          property="kpi_hours",
 *          type="integer",
 *          description="KPI hours",
 *          example=24
 *     ),
 *     @OA\Property(
 *          property="kpi_include_afterhours",
 *          type="boolean",
 *          description="Indicates whether a task with this type can include afterhours",
 *          example=false
 *     ),
 *     @OA\Property(
 *          property="color",
 *          type="integer",
 *          description="Defines the color of frames for tasks of this type",
 *          example=16777215
 *     )
 * )
 *
 * @package App\Http\Requests\Jobs
 */
class UpdateJobTaskTypeRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'                     => 'string|unique:job_task_types',
            'can_be_scheduled'         => 'boolean',
            'default_duration_minutes' => 'integer',
            'kpi_hours'                => 'integer|nullable',
            'kpi_include_afterhours'   => 'boolean|nullable',
            'color'                    => 'integer|nullable',
        ];
    }
}
