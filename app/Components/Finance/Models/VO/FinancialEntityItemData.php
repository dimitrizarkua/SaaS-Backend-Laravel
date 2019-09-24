<?php

namespace App\Components\Finance\Models\VO;

use App\Core\JsonModel;

/**
 * Class FinancialEntityItemData
 *
 * @package App\Components\Finance\Models\VO
 */
class FinancialEntityItemData extends JsonModel
{
    /**
     * @var int
     */
    public $gs_code_id;

    /**
     * @var string
     */
    public $description;

    /**
     * @var float
     */
    public $unit_cost;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var int
     */
    public $gl_account_id;

    /**
     * @var int
     */
    public $tax_rate_id;
}
