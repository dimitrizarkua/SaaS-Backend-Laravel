<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class FullJobEquipmentResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\JobEquipment
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/JobEquipment")},
 * )
 */
class FullJobEquipmentResource extends JsonResource
{
    /**
     * @OA\Property(
     *     type="object",
     *     property="equipment",
     *     allOf={@OA\Schema(ref="#/components/schemas/Equipment")},
     *     @OA\Property(
     *          property="location",
     *          ref="#/components/schemas/Location"
     *     ),
     * ),
     * @OA\Property(
     *     property="total_charge",
     *     description="Total charge",
     *     type="number",
     *     format="float",
     *     example=50.85,
     * ),
     * @OA\Property(
     *     property="charging_interval",
     *     ref="#/components/schemas/JobEquipmentChargingInterval"
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
        $result                          = $this->resource->toArray();
        $result['equipment']             = $this->equipment->toArray();
        $result['equipment']['location'] = $this->equipment->location_id
            ? $this->equipment->location->toArray()
            : null;
        $result['total_charge']          = round($this->getTotalCharge(), 2);
        $result['charging_interval']     = $this->getDefaultChargingInterval();
        unset($result['charging_intervals']);

        return $result;
    }
}
