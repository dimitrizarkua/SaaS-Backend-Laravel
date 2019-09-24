<?php

namespace App\Http\Requests\Finance;

use App\Components\Finance\Enums\InvoiceVirtualStatuses;

/**
 * Class SearchInvoicesRequest
 *
 * @package App\Http\Requests\Finance
 */
class SearchInvoicesRequest extends SearchFinancialEntityRequest
{
    /**
     * @inheritDoc
     */
    protected function getVirtualStatuses(): array
    {
        return InvoiceVirtualStatuses::values();
    }
}
