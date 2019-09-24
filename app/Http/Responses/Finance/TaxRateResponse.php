<?php

namespace App\Http\Responses\Finance;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class TaxRateResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class TaxRateResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     ref="#/components/schemas/TaxRate"
     * ),
     * @var \App\Components\Finance\Models\TaxRate
     */
    protected $data;
}
