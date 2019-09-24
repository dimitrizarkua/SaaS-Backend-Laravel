<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\CreditNoteListResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class CreditNotesSearchResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class CreditNotesSearchResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/CreditNoteListResource")
     * ),
     * @var CreditNoteListResource[]
     */
    protected $data;
}
