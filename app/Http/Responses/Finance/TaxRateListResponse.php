<?php

namespace App\Http\Responses\Finance;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class TaxRateListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class TaxRateListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/TaxRate")
     * ),
     * @var \App\Components\Finance\Models\TaxRate[]
     */
    protected $data;
}
