<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class TeamCountResource
 *
 * @package App\Components\Jobs\Resources
 * @property \App\Components\Jobs\Interfaces\TeamWithJobsCounterInterface $resource
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","name","jobs_count"},
 * )
 */
class TeamCountResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     description="Team identifier",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="name",
     *     description="Team name",
     *     type="string",
     *     example="Some team",
     * ),
     * @OA\Property(
     *     property="jobs_count",
     *     type="integer",
     *     description="Count of jobs for teams",
     *     example="10",
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
            'id'         => $this->resource->getTeamId(),
            'name'       => $this->resource->getTeamName(),
            'jobs_count' => $this->resource->getJobsCount(),
        ];
    }
}
