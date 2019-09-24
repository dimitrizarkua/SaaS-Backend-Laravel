<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\PurchaseOrderApproveRequestResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderApproveRequestListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class PurchaseOrderApproveRequestListResponse extends ApiOKResponse
{
    protected $resource = PurchaseOrderApproveRequestResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/PurchaseOrderApproveRequestResource")
     * ),
     * @var \App\Components\Finance\Models\PurchaseOrderApproveRequest[]
     */
    protected $data;
}
