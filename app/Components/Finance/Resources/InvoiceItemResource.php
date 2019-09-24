<?php

namespace App\Components\Finance\Resources;

use OpenApi\Annotations as OA;

/**
 * Class InvoiceItemResource
 *
 * @package App\Components\Finance\Resources
 * @mixin \App\Components\Finance\Models\InvoiceItem
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={
 *          @OA\Schema(ref="#/components/schemas/InvoiceItem"),
 *          @OA\Schema(ref="#/components/schemas/FinancialEntityItemResource"),
 *     },
 * )
 */
class InvoiceItemResource extends FinancialEntityItemResource
{
}
