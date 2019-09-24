<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\InvoiceResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class InvoiceResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class InvoiceResponse extends ApiOKResponse
{
    protected $resource = InvoiceResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/InvoiceResource"
     * ),
     * @var \App\Components\Finance\Resources\InvoiceResource
     */
    protected $data;
}
