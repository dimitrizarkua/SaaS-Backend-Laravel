<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobTaskListResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\JobTask
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/JobTask")},
 * )
 */
class JobTaskListResource extends JsonResource
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

    public function toArray($request)
    {
        $result = $this->resource->toArray();

        $result['assigned_users']          = JobTaskUserListResource::collection($this->assignedUsers);
        $result['assigned_teams']          = JobTaskTeamListResource::collection($this->assignedTeams);
        $result['latest_status']           = $this->latestStatus;
        $result['latest_scheduled_status'] = $this->latestScheduledStatus;
        $result['type']                    = $this->type;

        return $result;
    }
}
