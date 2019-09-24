<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\InvoicesListResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class InvoicesSearchResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class InvoicesSearchResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/InvoicesListResource")
     * ),
     * @var InvoicesListResource[]
     */
    protected $data;
}
