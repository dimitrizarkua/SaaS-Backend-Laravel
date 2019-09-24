<?php

namespace App\Http\Responses\Operations;

use App\Components\Operations\Resources\RunTemplateRunListResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class RunTemplateRunListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class RunTemplateRunListResponse extends ApiOKResponse
{
    protected $resource = RunTemplateRunListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/RunTemplateRunListResource")
     * ),
     * @var \App\Components\Operations\Resources\RunTemplateRunListResource
     */
    protected $data;
}
