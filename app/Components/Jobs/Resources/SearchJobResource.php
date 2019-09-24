<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class SearchJobResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\Job
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/Job")},
 *     @OA\Property(
 *          property="insurer",
 *          ref="#/components/schemas/Contact"
 *     ),
 *     @OA\Property(
 *          property="insurer_contract",
 *          ref="#/components/schemas/InsurerContract"
 *     ),
 *     @OA\Property(
 *          property="statuses",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/JobStatus")
 *     ),
 *     @OA\Property(
 *          property="latest_status",
 *          ref="#/components/schemas/JobStatus"
 *     ),
 *     @OA\Property(
 *          property="service",
 *          ref="#/components/schemas/JobService"
 *     ),
 *     @OA\Property(
 *          property="assigned_location",
 *          ref="#/components/schemas/Location"
 *     ),
 *     @OA\Property(
 *          property="owner_location",
 *          ref="#/components/schemas/Location"
 *     ),
 *     @OA\Property(
 *          property="site_address",
 *          ref="#/components/schemas/Address"
 *     ),
 *     @OA\Property(
 *          property="followers",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/User")
 *     ),
 *     @OA\Property(
 *          property="invoice_to_contact",
 *          type="object",
 *          required={"contact_id","contact_name"},
 *          description="Contact assigned to the job and marked as available to be invoiced.",
 *          @OA\Property(
 *               property="contact_id",
 *               type="integer",
 *               description="Contact identifier.",
 *               example="1",
 *          ),
 *          @OA\Property(
 *               property="contact_name",
 *               type="string",
 *               description="Legal name of company or full name of person (depends on contact type).",
 *               example="John Smith"
 *          )
 *     ),
 * )
 */
class SearchJobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource['data'];
    }
}
