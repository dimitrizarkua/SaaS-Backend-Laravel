<?php

namespace App\Components\Operations\Resources;

use App\Components\Contacts\Resources\ContactResource;
use App\Components\Jobs\Resources\JobTaskListResource;
use OpenApi\Annotations as OA;

/**
 * Class TaskListResource
 *
 * @package App\Components\Operations\Resources
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/JobTaskListResource")},
 * )
 */
class TaskListResource extends JobTaskListResource
{
    /**
     * @OA\Property(
     *     property="job",
     *     type="object",
     *     required={"id"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Job identifier",
     *         example=1
     *     ),
     *     @OA\Property(
     *         property="site_address",
     *         ref="#/components/schemas/FullAddressResource",
     *     ),
     *     @OA\Property(
     *         property="site_address_lat",
     *         description="Latitude of site address",
     *         type="number",
     *         example="-37.815018"
     *     ),
     *     @OA\Property(
     *         property="site_address_lng",
     *         description="Longitude of site address",
     *         type="number",
     *         example="144.946014"
     *     ),
     *     @OA\Property(
     *         property="customer",
     *         ref="#/components/schemas/ContactResource"
     *     ),
     * )
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
        $result        = parent::toArray($request);
        $result['job'] = null;

        if (null !== $this->resource->job) {
            $siteContact = $this->resource->job->getSiteContact();

            $result['job'] = [
                'id'               => $this->resource->job->id,
                'site_address'     => $this->resource->job->siteAddress,
                'site_address_lat' => $this->resource->job->site_address_lat,
                'site_address_lng' => $this->resource->job->site_address_lng,
                'site_contact'     => $siteContact ? ContactResource::make($siteContact) : null,
            ];
        }

        return $result;
    }
}
