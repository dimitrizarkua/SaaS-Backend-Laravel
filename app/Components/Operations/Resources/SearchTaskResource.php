<?php

namespace App\Components\Operations\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class SearchTaskResource
 *
 * @package App\Components\Operations\Resources
 * @mixin \App\Components\Jobs\Models\JobTask
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/JobTask")},
 * )
 */
class SearchTaskResource extends JsonResource
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
        return $this->resource['data'];
    }
}
