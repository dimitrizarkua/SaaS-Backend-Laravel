<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\InvoiceItemResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class InvoicesItemResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class InvoicesItemResponse extends ApiOKResponse
{
    protected $resource = InvoiceItemResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/InvoiceItemResource"
     * ),
     * @var InvoiceItemResource
     */
    protected $data;
}
