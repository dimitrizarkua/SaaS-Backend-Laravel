<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\CreditNoteItemResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class CreditNoteItemResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class CreditNoteItemResponse extends ApiOKResponse
{
    protected $resource = CreditNoteItemResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/CreditNoteItemResource"
     * ),
     * @var \App\Components\Finance\Resources\CreditNoteItemResource
     */
    protected $data;
}
