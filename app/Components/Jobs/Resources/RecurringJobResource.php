<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RecurringJobResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\RecurringJob
 *
 * @OA\Schema(
 *     type="object",
 *     required={
 *     "id",
 *     "recurrence_rule",
 *     "insurer_id",
 *     "job_service_id",
 *     "site_address_id",
 *     "owner_location_id",
 *     "description"
 * })
 */
class RecurringJobResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="id",
     *     description="Recurring job identifier. Equals to job identifier.",
     *     type="integer",
     *     example="1"
     * )
     * @OA\Property(
     *     property="recurrence_rule",
     *     description="Recurrence rule according to https://tools.ietf.org/html/rfc5545.",
     *     type="string",
     *     example=""
     * ),
     * @OA\Property(
     *     property="job_service_id",
     *     description="Identifier of related service",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="site_address_id",
     *     description="Site address identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="owner_location_id",
     *     description="Owner location identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="description",
     *     description="Recurring job description",
     *     type="string",
     *     example="Recurring job description"
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
        $result = collect($this->resource->toArray());

        if (isset($result['pivot'])) {
            unset($result['pivot']);
        }

        return $result;
    }
}
