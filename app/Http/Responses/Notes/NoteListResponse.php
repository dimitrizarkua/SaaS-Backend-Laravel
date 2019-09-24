<?php

namespace App\Http\Responses\Notes;

use App\Http\Responses\ApiOKResponse;

/**
 * Class NoteListResponse
 *
 * @package App\Http\Responses\Notes
 * @OA\Schema(required={"data"})
 */
class NoteListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Note")
     * )
     *
     * @var \App\Components\Notes\Models\Note[]
     */
    protected $data;
}
