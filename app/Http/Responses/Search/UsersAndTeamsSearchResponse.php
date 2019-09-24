<?php

namespace App\Http\Responses\Search;

use App\Components\Search\Resources\UsersAndTeamsGroupedResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class UsersAndTeamsSearchResponse
 *
 * @package App\Http\Responses\Search
 * @OA\Schema(required={"data"})
 */
class UsersAndTeamsSearchResponse extends ApiOKResponse
{
    protected $resource = UsersAndTeamsGroupedResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(
     *          ref="#/components/schemas/UsersAndTeamsGroupedResource"
     *     )
     * )
     *
     * @var UsersAndTeamsGroupedResource[]
     */
    protected $data;
}
