<?php

namespace App\Components\UsageAndActuals\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class MaterialResource
 *
 * @package App\Components\UsageAndActuals\Resources
 * @mixin \App\Components\UsageAndActuals\Models\Material
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/Material")},
 * )
 */
class MaterialResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="measure_unit",
     *     ref="#/components/schemas/MeasureUnit"
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
        $result                 = $this->resource->toArray();
        $result['measure_unit'] = $this->measureUnit;

        return $result;
    }
}
