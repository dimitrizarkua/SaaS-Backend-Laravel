<?php

namespace App\Http\Responses\Finance;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class PaymentsListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class PaymentsListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Payment")
     * ),
     * @var \App\Components\Finance\Models\Payment[]
     */
    protected $data;
}
