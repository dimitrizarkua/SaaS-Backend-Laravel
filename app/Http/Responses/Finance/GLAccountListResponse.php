<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\GLAccountResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class GLAccountListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class GLAccountListResponse extends ApiOKResponse
{
    protected $resource = GLAccountResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/GLAccountResource")
     * ),
     * @var \App\Components\Finance\Resources\GLAccountResource[]
     */
    protected $data;
}
