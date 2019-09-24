<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\ApproverResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class ApproverListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class ApproverListResponse extends ApiOKResponse
{
    protected $resource = ApproverResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/ApproverResource")
     * ),
     * @var ApproverResource[]
     */
    protected $data;
}
