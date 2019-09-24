<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobEquipmentTotalAmountResource
 *
 * @package App\Components\Jobs\Resources
 *
 * @OA\Schema(type="object")
 */
class JobEquipmentTotalAmountResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="total_amount",
     *     description="Total amount of equipment assigned to job",
     *     type="number",
     *     format="float",
     *     example=2500.75,
     * ),
     * @OA\Property(
     *     property="total_amount_for_insurer",
     *     description="Total amount of equipment assigned to job consider to insurer contract",
     *     type="number",
     *     format="float",
     *     example=1920.50,
     * ),
     */

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $result                             = $this->resource;
        $result['total_amount']             = round($result['total_amount'], 2);
        $result['total_amount_for_insurer'] = round($result['total_amount_for_insurer'], 2);

        return $result;
    }
}
