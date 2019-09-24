<?php

namespace App\Components\Notes\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class NoteUserListResource
 *
 * @package App\Components\Notes\Resources
 * @mixin \App\Components\Notes\Models\Note
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/User")},
 * )
 */
class NoteUserListResource extends JsonResource
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
