<?php

namespace App\Components\Finance\Resources;

use OpenApi\Annotations as OA;

/**
 * Class CreditNoteItemResource
 *
 * @package App\Components\Finance\Resources
 *
 * @OA\Schema(
 *     allOf={
 *          @OA\Schema(ref="#/components/schemas/CreditNoteItem"),
 *          @OA\Schema(ref="#/components/schemas/FinancialEntityItemResource"),
 *     },
 * )
 */
class CreditNoteItemResource extends FinancialEntityItemResource
{
}
