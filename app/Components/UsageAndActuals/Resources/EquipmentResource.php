<?php

namespace App\Components\UsageAndActuals\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class EquipmentResource
 *
 * @package App\Components\UsageAndActuals\Resources
 * @mixin \App\Components\UsageAndActuals\Models\Equipment
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/Equipment")},
 * )
 */
class EquipmentResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="category",
     *     ref="#/components/schemas/EquipmentCategory",
     * ),
     * @OA\Property(
     *     property="charging_interval",
     *     ref="#/components/schemas/EquipmentCategoryChargingInterval",
     * ),
     * @OA\Property(
     *     property="location",
     *     ref="#/components/schemas/Location",
     * ),
     */

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        $result                      = $this->resource->toArray();
        $result['category']          = $this->category->toArray();
        $result['charging_interval'] = $this->getDefaultChargingInterval();
        $result['location']          = null === $this->location_id ?: $this->location->toArray();

        return $result;
    }
}
