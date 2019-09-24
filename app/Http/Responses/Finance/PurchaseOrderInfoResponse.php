<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\PurchaseOrderInfoResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderInfoResponse
 *
 * @package App\Http\Responses\Finance
 *
 * @OA\Schema(required={"data"})
 */
class PurchaseOrderInfoResponse extends ApiOKResponse
{
    protected $resource = PurchaseOrderInfoResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/PurchaseOrderInfoResource"
     * )
     *
     * @var PurchaseOrderInfoResource
     */
    protected $data;
}
