<?php

namespace App\Components\Jobs\Resources;

use App\Components\Jobs\Interfaces\JobCountersInterface;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobsInfoResource
 *
 * @package App\Components\Jobs\Resources
 * @property JobCountersInterface $resource
 *
 * @OA\Schema(
 *     type="object",
 *     required={"inbox","mine","active","closed","teams","no_contact_24_hours","upcoming_kpi"},
 * )
 */
class JobsInfoResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="inbox",
     *     type="integer",
     *     description="Count of jobs in the Inbox tab"
     * ),
     * @OA\Property(
     *     property="mine",
     *     type="integer",
     *     description="Count of jobs in the Mine tab"
     * ),
     * @OA\Property(
     *     property="active",
     *     type="integer",
     *     description="Count of jobs in the active tab"
     * ),
     * @OA\Property(
     *     property="closed",
     *     type="integer",
     *     description="Count of jobs in the Closed tab"
     * ),
     * @OA\Property(
     *     property="teams",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/TeamCountResource")
     * ),
     * @OA\Property(
     *     property="no_contact_24_hours",
     *     type="integer",
     *     description="No contact 24 hours"
     * ),
     * @OA\Property(
     *     property="upcoming_kpi",
     *     type="integer",
     *     description="Upcoming KPI"
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
        return [
            'inbox'               => $this->resource->getInboxCount(),
            'mine'                => $this->resource->getMineCount(),
            'active'              => $this->resource->getAllActiveJobsCount(),
            'closed'              => $this->resource->getClosedCount(),
            'teams'               => TeamCountResource::collection($this->resource->getTeams()),
            'no_contact_24_hours' => $this->resource->getNoContact24HoursCount(),
            'upcoming_kpi'        => $this->resource->getUpcomingKPICount(),
        ];
    }
}
