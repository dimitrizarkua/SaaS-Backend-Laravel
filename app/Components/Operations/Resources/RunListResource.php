<?php

namespace App\Components\Operations\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class RunListResource
 *
 * @package App\Components\Operations\Resources
 * @mixin \App\Components\Operations\Models\JobRun
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/JobRun")},
 * )
 */
class RunListResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="assigned_users",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/RunUserListResource")
     * ),
     * @OA\Property(
     *     property="assigned_vehicles",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/RunVehicleListResource")
     * ),
     * @OA\Property(
     *     property="assigned_tasks",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/RunTaskListResource")
     * ),
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $result = $this->resource->toArray();

        $result['assigned_users']    = RunUserListResource::collection($this->assignedUsers);
        $result['assigned_vehicles'] = RunVehicleListResource::collection($this->assignedVehicles);
        $result['assigned_tasks']    = RunTaskListResource::collection($this->assignedTasks);

        return $result;
    }
}
