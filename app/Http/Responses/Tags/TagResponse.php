<?php

namespace App\Http\Responses\Tags;

use App\Http\Responses\ApiOKResponse;

/**
 * Class TagResponse
 *
 * @package App\Http\Responses\Tags
 * @OA\Schema(required={"data"})
 */
class TagResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/Tag")
     * @var \App\Components\Tags\Models\Tag
     */
    protected $data;
}
