<?php

namespace App\Http\Responses\Operations;

use App\Components\Operations\Resources\RunListResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class RunListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class RunListResponse extends ApiOKResponse
{
    protected $resource = RunListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/RunListResource")
     * ),
     * @var \App\Components\Operations\Resources\RunListResource[]
     */
    protected $data;
}
