<?php

namespace App\Components\Contacts\Resources;

use App\Components\Notes\Resources\FullNoteResource;
use OpenApi\Annotations as OA;

/**
 * Class ContactNoteResource
 *
 * @OA\Schema(
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ContactNoteListResource"),
 *         @OA\Schema(ref="#/components/schemas/FullNoteResource"),
 *     },
 * )
 *
 * @package App\Components\Contacts\Resources
 */
class ContactNoteResource extends FullNoteResource
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
        $result             = parent::toArray($request);
        $result['contacts'] = ContactResource::collection($this->contacts);

        return $result;
    }
}
