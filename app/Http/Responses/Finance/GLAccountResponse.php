<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\GLAccountResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class GLAccountResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class GLAccountResponse extends ApiOKResponse
{
    protected $resource = GLAccountResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/GLAccountResource"
     * ),
     * @var \App\Components\Finance\Resources\GLAccountResource
     */
    protected $data;
}
