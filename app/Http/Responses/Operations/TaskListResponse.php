<?php

namespace App\Http\Responses\Operations;

use App\Components\Operations\Resources\TaskListResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class TaskListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class TaskListResponse extends ApiOKResponse
{
    protected $resource = TaskListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/TaskListResource")
     * ),
     * @var \App\Components\Operations\Resources\TaskListResource[]
     */
    protected $data;
}
