<?php

namespace App\Components\UsageAndActuals\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class EquipmentCategoryResource
 *
 * @package App\Components\UsageAndActuals\Resources
 * @mixin \App\Components\UsageAndActuals\Models\EquipmentCategory
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/EquipmentCategory")},
 * )
 */
class EquipmentCategoryResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="charging_intervals",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/EquipmentCategoryChargingInterval")
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
        $result                       = $this->resource->toArray();
        $result['charging_intervals'] = $this->defaultChargingIntervals;

        return $result;
    }
}
