<?php

namespace App\Components\Finance;

use App\DefaultIndexConfigurator;

/**
 * Class PurchaseOrderIndexConfigurator
 *
 * @package App\Components\Finance
 */
class PurchaseOrderIndexConfigurator extends DefaultIndexConfigurator
{
    /**
     * Name of the index.
     *
     * @var string
     */
    protected $name = 'purchase_orders_index';
}
