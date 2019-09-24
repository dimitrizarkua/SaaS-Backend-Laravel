<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\PurchaseOrderItemResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderItemListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class PurchaseOrderItemListResponse extends ApiOKResponse
{
    protected $resource = PurchaseOrderItemResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/PurchaseOrderItemResource")
     * ),
     * @var \App\Components\Finance\Resources\PurchaseOrderItemResource[]
     */

    protected $data;
}
