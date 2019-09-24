<?php

namespace App\Http\Requests\Finance;

use App\Components\Finance\Enums\CreditNoteVirtualStatuses;

/**
 * Class SearchCreditNotesRequest
 *
 * @package App\Http\Requests\Finance
 */
class SearchCreditNotesRequest extends SearchFinancialEntityRequest
{
    /**
     * @inheritDoc
     */
    protected function getVirtualStatuses(): array
    {
        return CreditNoteVirtualStatuses::values();
    }
}
