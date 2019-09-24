<?php

namespace App\Components\Finance\Models\VO;

use App\Core\JsonModel;

/**
 * Class PaymentInvoiceItem
 *
 * @package App\Components\Finance\Models\VO
 */
class PaymentInvoiceItem extends JsonModel
{
    /**
     * @var int
     */
    public $invoice_id;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var bool
     */
    public $is_fp = false;
}
