<?php

namespace App\Components\Photos\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PhotoResource
 *
 * @package App\Components\Photos\Resources
 * @mixin \App\Components\Photos\Models\Photo
 *
 * @OA\Schema(
 *     type="object",
 *     nullable=true,
 *     allOf={@OA\Schema(ref="#/components/schemas/Photo")},
 * )
 */
class PhotoResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="thumbnails",
     *      type="array",
     *      description="Photo thumbnails",
     *      @OA\Items(ref="#/components/schemas/ThumbnailResource"),
     * )
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
        $result = $this->resource->toArray();

        $result['thumbnails'] = ThumbnailResource::collection($this->thumbnails);
        if (isset($result['pivot'])) {
            unset($result['pivot']);
        }

        return $result;
    }
}
