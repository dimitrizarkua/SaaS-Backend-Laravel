<?php

namespace App\Components\Finance\Resources;

use OpenApi\Annotations as OA;

/**
 * Class CreditNoteListResource
 *
 * @package App\Components\Finance\Resources
 * @mixin \App\Components\Finance\Models\CreditNoteItem
 *
 * @OA\Schema(
 *     @OA\Property(
 *        property="virtual_status",
 *        ref="#/components/schemas/CreditNoteVirtualStatuses"
 *     ),
 *     allOf={@OA\Schema(ref="#/components/schemas/FinanceEntityListResource")},
 * )
 *
 * @mixin \App\Components\Finance\Models\CreditNote
 */
class CreditNoteListResource extends FinanceEntityListResource
{
}
