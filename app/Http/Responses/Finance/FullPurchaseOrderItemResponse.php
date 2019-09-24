<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\PurchaseOrderItemResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullPurchaseOrderItemResponse
 *
 * @package App\Http\Responses\Finance
 *
 * @OA\Schema(required={"data"})
 */
class FullPurchaseOrderItemResponse extends ApiOKResponse
{
    protected $resource = PurchaseOrderItemResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/PurchaseOrderItemResource"
     * )
     *
     * @var PurchaseOrderItemResource
     */
    protected $data;
}
