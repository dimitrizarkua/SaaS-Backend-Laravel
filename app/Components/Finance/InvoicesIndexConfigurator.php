<?php

namespace App\Components\Finance;

use App\DefaultIndexConfigurator;

/**
 * Class InvoicesIndexConfigurator
 *
 * @package App\Components\Finance
 */
class InvoicesIndexConfigurator extends DefaultIndexConfigurator
{
    /**
     * Name of the index.
     *
     * @var string
     */
    protected $name = 'invoices_index';
}
