<?php

namespace App\Http\Responses\Finance;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderSearchResponse
 *
 * @package App\Http\Responses\Finance
 *
 * @OA\Schema(required={"data"})
 */
class PurchaseOrderSearchResponse extends ApiOKResponse
{
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
