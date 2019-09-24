<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class LinkedJobsListResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\LinkedJob
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","claim_number","created_at"}
 * )
 */
class LinkedJobsListResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="id",
     *     description="Job Identifier",
     *     type="integer",
     *     example="1"
     * )
     * @OA\Property(
     *     property="claim_number",
     *     description="Claim number",
     *     type="string",
     *     example="10198747-MEL"
     * )
     * @OA\Property(property="created_at", type="string", format="date-time")
     * @OA\Property(
     *     property="latest_status",
     *     ref="#/components/schemas/JobStatusResource",
     * )
     * @OA\Property(
     *     property="assigned_location",
     *     ref="#/components/schemas/Location",
     * )
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function toArray($request)
    {
        $collection = collect($this->resource->toArray());
        $result     = $collection->only(['claim_number', 'id', 'created_at']);

        if ($this->latestStatus) {
            $result['latest_status'] = JobStatusResource::make($this->latestStatus);
        }

        if ($this->assignedLocation) {
            $result['assigned_location'] = $this->assignedLocation;
        }

        if (isset($result['pivot'])) {
            unset($result['pivot']);
        }

        return $result;
    }
}
