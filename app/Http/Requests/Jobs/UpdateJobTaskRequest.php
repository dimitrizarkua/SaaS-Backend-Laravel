<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateJobTaskRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="job_task_type_id",
 *          type="integer",
 *          description="Job task type identifier",
 *          example=1
 *     ),
 *     @OA\Property(
 *          property="name",
 *          type="string",
 *          description="Name",
 *          example="Customer call"
 *     ),
 *     @OA\Property(
 *          property="internal_note",
 *          type="string",
 *          description="Internal note",
 *          example="Some text"
 *     ),
 *     @OA\Property(
 *          property="scheduling_note",
 *          type="string",
 *          description="Scheduling note",
 *          example="Some text"
 *     ),
 *     @OA\Property(
 *          property="kpi_missed_reason",
 *          type="string",
 *          description="Reason of why KPI missed",
 *          example="Some text"
 *     ),
 *     @OA\Property(
 *          property="due_at",
 *          description="Due at time",
 *          type="string",
 *          format="date-time",
 *          example="2018-11-10T09:10:11Z"
 *     ),
 * )
 *
 * @package App\Http\Requests\Jobs
 */
class UpdateJobTaskRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'job_task_type_id'  => 'integer',
            'name'              => 'nullable|string',
            'internal_note'     => 'nullable|string',
            'scheduling_note'   => 'nullable|string',
            'kpi_missed_reason' => 'nullable|string',
            'due_at'            => 'nullable|date_format:Y-m-d\TH:i:s\Z',
        ];
    }
}
