<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\CreditNoteResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class CreditNoteResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class CreditNoteResponse extends ApiOKResponse
{
    protected $resource = CreditNoteResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/CreditNoteResource"
     * ),
     * @var \App\Components\Finance\Resources\CreditNoteResource
     */
    protected $data;
}
