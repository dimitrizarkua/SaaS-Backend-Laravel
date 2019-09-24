<?php

namespace App\Http\Responses\Finance;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class CreditNoteApproveRequestResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class CreditNoteApproveRequestResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     ref="#/components/schemas/CreditNoteApproveRequest"
     * ),
     * @var \App\Components\Finance\Models\CreditNoteApproveRequest
     */
    protected $data;
}
