<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\PaymentDetailsResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullPaymentResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class FullPaymentResponse extends ApiOKResponse
{
    protected $resource = PaymentDetailsResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/PaymentDetailsResource"
     * ),
     * @var \App\Components\Finance\Resources\PaymentDetailsResource
     */
    protected $data;
}
