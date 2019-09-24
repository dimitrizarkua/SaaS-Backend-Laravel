<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\CreditNoteListResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class CreditNoteListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class CreditNoteListResponse extends ApiOKResponse
{
    protected $resource = CreditNoteListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/CreditNoteListResource")
     * )
     *
     * @var \App\Components\Finance\Resources\CreditNoteListResource[]
     */
    protected $data;
}
