<?php

namespace App\Http\Responses\Tags;

use App\Http\Responses\ApiOKResponse;

/**
 * Class TagListResponse
 *
 * @package App\Http\Responses\Tags
 * @OA\Schema(required={"data"})
 */
class TagListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Tag")
     * )
     *
     * @var \App\Components\Tags\Models\Tag[]
     */
    protected $data;
}
