<?php

namespace App\Components\Notes\Resources;

/**
 * Class FullNoteResource
 *
 * @package App\Components\Notes\Resources
 * @mixin \App\Components\Notes\Models\Note
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/NoteResource")},
 * )
 */
class FullNoteResource extends NoteResource
{
    /**
     * @OA\Property(
     *      property="documents",
     *      type="array",
     *      description="Attached documents",
     *      @OA\Items(ref="#/components/schemas/Document"),
     * )
     * @OA\Property(
     *      property="mentioned_users",
     *      type="array",
     *      description="Mentioned users",
     *      @OA\Items(ref="#/components/schemas/User"),
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
        $result = parent::toArray($request);

        $result['documents']       = $this->resource->documents;
        $result['mentioned_users'] = NoteUserListResource::collection($this->mentionedUsers);

        return $result;
    }
}
