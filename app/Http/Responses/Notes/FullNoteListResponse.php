<?php

namespace App\Http\Responses\Notes;

use App\Components\Notes\Resources\FullNoteResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullNoteListResponse
 *
 * @package App\Http\Responses\Notes
 * @OA\Schema(required={"data"})
 */
class FullNoteListResponse extends ApiOKResponse
{
    protected $resource = FullNoteResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/FullNoteResource")
     * )
     *
     * @var \App\Components\Notes\Resources\FullNoteResource[]
     */
    protected $data;
}
