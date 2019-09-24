<?php

namespace App\Components\Jobs\Resources;

use App\Components\UsageAndActuals\Resources\MaterialResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobMaterialResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\JobMaterial
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/JobMaterial")},
 * )
 */
class JobMaterialResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="material",
     *     ref="#/components/schemas/MaterialResource",
     * )
     * @OA\Property(property="amount", type="number", description="Amount", example="1.00")
     * @OA\Property(property="amount_override", type="number", description="Overridden amount", example="1.00")
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
        $result                    = $this->resource->toArray();
        $result['material']        = MaterialResource::make($this->material);
        $result['amount']          = $this->totalAmount();
        $result['amount_override'] = $this->totalAmountOverride();

        return $result;
    }
}
