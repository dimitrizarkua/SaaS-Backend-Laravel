<?php

namespace App\Components\Locations\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class LocationSuburbResource
 *
 * @package App\Components\Locations\Resources
 * @mixin \App\Components\Addresses\Models\Suburb
 *
 * @OA\Schema(ref="#/components/schemas/Suburb")
 */
class LocationSuburbResource extends JsonResource
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
        $result = $this->resource->toArray();

        if (isset($result['pivot'])) {
            unset($result['pivot']);
        }

        return $result;
    }
}
