<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class FullJobTaskResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\JobTask
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/JobTask")},
 * )
 */
class FullJobTaskResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="assigned_users",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobTaskUserListResource")
     * ),
     * @OA\Property(
     *     property="assigned_teams",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobTaskTeamListResource")
     * ),
     * @OA\Property(
     *     property="assigned_vehicles",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobTaskVehicleListResource")
     * ),
     * @OA\Property(
     *     property="latest_status",
     *     ref="#/components/schemas/JobTaskStatus",
     * ),
     * @OA\Property(
     *     property="latest_scheduled_status",
     *     ref="#/components/schemas/JobTaskScheduledPortionStatus",
     * ),
     * @OA\Property(
     *     property="type",
     *     ref="#/components/schemas/JobTaskType",
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

        return array_merge($result, [
            'assigned_users'          => JobTaskUserListResource::collection($this->assignedUsers),
            'assigned_teams'          => JobTaskTeamListResource::collection($this->assignedTeams),
            'assigned_vehicles'       => JobTaskVehicleListResource::collection($this->assignedVehicles),
            'latest_status'           => $this->latestStatus,
            'latest_scheduled_status' => $this->latestScheduledStatus,
            'type'                    => $this->type,
        ]);
    }
}
