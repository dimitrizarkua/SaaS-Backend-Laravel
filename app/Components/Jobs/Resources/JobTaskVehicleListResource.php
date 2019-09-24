<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobTaskVehicleListResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Operations\Models\Vehicle
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/Vehicle")},
 * )
 */
class JobTaskVehicleListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $result = $this->resource->toArray();

        if (isset($result['pivot'])) {
            unset($result['pivot']);
        }

        return $result;
    }
}
