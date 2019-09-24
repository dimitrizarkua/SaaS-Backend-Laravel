<?php

namespace App\Http\Responses\Notes;

use App\Components\Notes\Resources\NoteResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class NoteResponse
 *
 * @package App\Http\Responses\Notes
 * @OA\Schema(required={"data"})
 */
class NoteResponse extends ApiOKResponse
{
    protected $resource = NoteResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/NoteResource")
     * @var \App\Components\Notes\Resources\NoteResource
     */
    protected $data;
}
