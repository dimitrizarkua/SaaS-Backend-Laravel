<?php

namespace App\Components\Operations\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class FullRunTemplateResource
 *
 * @package App\Components\Operations\Resources
 * @mixin \App\Components\Operations\Models\JobRunTemplate
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/JobRunTemplate")},
 * )
 */
class FullRunTemplateResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="runs",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/RunTemplateRunListResource")
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
        $result = $this->resource->toArray();

        $result['runs'] = RunTemplateRunListResource::collection($this->runs);

        return $result;
    }
}
