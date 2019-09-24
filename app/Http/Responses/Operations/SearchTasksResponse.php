<?php

namespace App\Http\Responses\Operations;

use App\Components\Operations\Resources\SearchTaskResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class SearchTasksResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class SearchTasksResponse extends ApiOKResponse
{
    protected $resource = SearchTaskResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/SearchTaskResource")
     * ),
     * @var \App\Components\Operations\Resources\SearchTaskResource[]
     */
    protected $data;
}
