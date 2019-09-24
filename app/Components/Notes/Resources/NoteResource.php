<?php

namespace App\Components\Notes\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class NoteResource
 *
 * @package App\Components\Notes\Resources
 * @mixin \App\Components\Notes\Models\Note
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/Note")},
 * )
 */
class NoteResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="user",
     *      type="object",
     *      description="Note author",
     *      ref="#/components/schemas/User",
     * )
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
        $result         = $this->resource->toArray();
        $result['user'] = $this->resource->user;

        if (isset($result['pivot'])) {
            unset($result['pivot']);
        }

        unset($result['mentioned_users']);

        return $result;
    }
}
