<?php

namespace App\Components\Documents\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class DocumentResource
 *
 * @package App\Components\Documents\Resources
 * @mixin \App\Components\Documents\Models\Document
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/Document")},
 * )
 */
class DocumentResource extends JsonResource
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
