<?php

namespace App\Components\Finance\Resources;

use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderItemResource
 *
 * @package App\Components\Finance\Resources
 *
 * @OA\Schema(
 *     allOf={
 *          @OA\Schema(ref="#/components/schemas/PurchaseOrderItem"),
 *          @OA\Schema(ref="#/components/schemas/FinancialEntityItemResource"),
 *     },
 * )
 */
class PurchaseOrderItemResource extends FinancialEntityItemResource
{
}
