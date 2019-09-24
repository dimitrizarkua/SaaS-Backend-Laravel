<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\PurchaseOrderListResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class PurchaseOrderListResponse extends ApiOKResponse
{
    protected $resource = PurchaseOrderListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/PurchaseOrderListResource")
     * ),
     * @var \App\Components\Finance\Resources\PurchaseOrderListResource[]
     */

    protected $data;
}
