<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\PurchaseOrderSuggestedApproverResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderSuggestedApproverListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class PurchaseOrderSuggestedApproverListResponse extends ApiOKResponse
{
    protected $resource = PurchaseOrderSuggestedApproverResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/PurchaseOrderSuggestedApproverResource")
     * ),
     */
    protected $data;
}
