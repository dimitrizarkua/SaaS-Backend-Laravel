<?php

namespace App\Components\Photos\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ThumbnailResource
 *
 * @package App\Components\Photos\Resources
 * @mixin \App\Components\Photos\Models\Photo
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/Photo")},
 * )
 */
class ThumbnailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return $this->resource->toArray();
    }
}
