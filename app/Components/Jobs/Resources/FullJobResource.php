<?php

namespace App\Components\Jobs\Resources;

use App\Components\Addresses\Resources\FullAddressResource;
use App\Components\Contacts\Resources\ContactResource;
use App\Components\Core\Resources\UserWithLocationsResource;
use App\Components\Locations\Resources\UserLocationResource;
use App\Components\UsageAndActuals\Resources\InsurerContractResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class FullJobResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\Job
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/Job")},
 * )
 */
class FullJobResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="service",
     *     ref="#/components/schemas/JobService",
     * ),
     * @OA\Property(
     *     property="insurer",
     *     ref="#/components/schemas/ContactResource",
     * ),
     * @OA\Property(
     *     property="insurer_contract",
     *     ref="#/components/schemas/InsurerContractResource",
     * ),
     * @OA\Property(
     *     property="site_address",
     *     ref="#/components/schemas/FullAddressResource",
     * ),
     * @OA\Property(
     *     property="assigned_location",
     *     ref="#/components/schemas/Location",
     * ),
     * @OA\Property(
     *     property="owner_location",
     *     ref="#/components/schemas/Location",
     * ),
     * @OA\Property(
     *     property="statuses",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobStatusResource")
     * ),
     * @OA\Property(
     *     property="latest_status",
     *     ref="#/components/schemas/JobStatusResource",
     * ),
     * @OA\Property(
     *     property="tags",
     *     type="array",
     *     @OA\Items(
     *          ref="#/components/schemas/Tag",
     *     )
     * ),
     * @OA\Property(
     *     property="linked_jobs",
     *     type="array",
     *     @OA\Items(
     *          ref="#/components/schemas/LinkedJobsListResource",
     *     )
     * ),
     * @OA\Property(
     *     property="previous_jobs",
     *     type="array",
     *     @OA\Items(
     *          ref="#/components/schemas/JobListResource",
     *     )
     * ),
     * @OA\Property(
     *     property="job_users",
     *     type="array",
     *     @OA\Items(
     *          ref="#/components/schemas/User",
     *     )
     * ),
     * @OA\Property(
     *     property="job_teams",
     *     type="array",
     *     @OA\Items(
     *          ref="#/components/schemas/Team",
     *     )
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
        $result         = $this->resource->toArray();
        $additionalData = [
            'service'           => $this->service,
            'insurer'           => ContactResource::make($this->insurer),
            'insurer_contract'  => InsurerContractResource::make($this->insurerContract),
            'site_address'      => FullAddressResource::make($this->siteAddress),
            'assigned_location' => $this->assignedLocation,
            'owner_location'    => $this->ownerLocation,
            'statuses'          => JobStatusResource::collection($this->statuses),
            'latest_status'     => JobStatusResource::make($this->latestStatus),
            'followers'         => $this->followers,
            'tags'              => $this->tags,
            'linked_jobs'       => LinkedJobsListResource::collection($this->linkedJobs),
            'previous_jobs'     => JobListResource::collection($this->getPreviousJobs()),
            'job_users'         => UserWithLocationsResource::collection($this->assignedUsers),
            'job_teams'         => $this->assignedTeams,
        ];

        return array_merge($result, $additionalData);
    }
}
