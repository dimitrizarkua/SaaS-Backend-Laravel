<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\PurchaseOrderItemResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderItemResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class PurchaseOrderItemResponse extends ApiOKResponse
{
    protected $resource = PurchaseOrderItemResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/PurchaseOrderItem"
     * ),
     * @var PurchaseOrderItemResource
     */
    protected $data;
}
