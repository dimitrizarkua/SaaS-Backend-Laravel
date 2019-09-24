<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class JobNotesAndMessagesResource
 *
 * @package App\Components\Jobs\Resources
 *
 * @OA\Schema(type="object")
 */
class JobNotesAndMessagesResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="notes",
     *      type="array",
     *      description="Job notes",
     *      @OA\Items(ref="#/components/schemas/FullJobNoteResource"),
     * )
     * @OA\Property(
     *      property="messages",
     *      type="array",
     *      description="Job messages",
     *      @OA\Items(ref="#/components/schemas/FullJobMessageResource"),
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
        $result = $this->resource;

        $result['notes'] = FullJobNoteResource::collection($result['notes']);
        $result['messages'] = FullJobMessageResource::collection($result['messages']);

        return $result;
    }
}
