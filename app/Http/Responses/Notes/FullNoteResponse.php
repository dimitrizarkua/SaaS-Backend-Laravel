<?php

namespace App\Http\Responses\Notes;

use App\Components\Notes\Resources\FullNoteResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullNoteResponse
 *
 * @package App\Http\Responses\Notes
 * @OA\Schema(required={"data"})
 */
class FullNoteResponse extends ApiOKResponse
{
    protected $resource = FullNoteResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/FullNoteResource")
     * @var \App\Components\Notes\Models\Note
     */
    protected $data;
}
