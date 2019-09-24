<?php

namespace App\Http\Requests\Finance;

use App\Components\Finance\Enums\PurchaseOrderVirtualStatuses;

/**
 * Class SearchPurchaseOrderRequest
 *
 * @package App\Http\Requests\Finance
 */
class SearchPurchaseOrderRequest extends SearchFinancialEntityRequest
{
    /**
     * @inheritDoc
     */
    protected function getVirtualStatuses(): array
    {
        return PurchaseOrderVirtualStatuses::values();
    }
}
