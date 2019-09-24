<?php

namespace App\Components\Finance\Models\VO;

use App\Core\JsonModel;

/**
 * Class PaymentReceipt
 *
 * @package App\Components\Finance\Models\VO
 */
class PaymentReceipt extends JsonModel
{
    /**
     * @var int|null
     */
    public $jobId;

    /**
     * @var \Illuminate\Support\Carbon
     */
    public $paidAt;

    /**
     * @var string
     */
    public $externalTransactionId;

    /**
     * @var float
     */
    public $amount;
}
