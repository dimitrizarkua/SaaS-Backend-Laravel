<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\PurchaseOrderResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderResponse
 *
 * @package App\Http\Responses\Finance
 *
 * @OA\Schema(required={"data"})
 */
class PurchaseOrderResponse extends ApiOKResponse
{
    protected $resource = PurchaseOrderResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/PurchaseOrderResource"
     * )
     *
     * @var PurchaseOrderResource
     */
    protected $data;
}
