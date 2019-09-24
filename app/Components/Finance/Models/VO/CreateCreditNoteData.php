<?php

namespace App\Components\Finance\Models\VO;

/**
 * Class CreateCreditNoteData
 *
 * @package App\Components\Finance\Models\VO
 */
class CreateCreditNoteData extends CreateFinancialEntityData
{
    /**
     * @var CreditNoteItemData[]
     */
    public $items = [];

    /**
     * @var int|null
     */
    public $payment_id;
}
