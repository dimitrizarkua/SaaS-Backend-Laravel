<?php

namespace App\Components\Finance;

use App\DefaultIndexConfigurator;

/**
 * Class CreditNotesIndexConfigurator
 *
 * @package App\Components\Finance
 */
class CreditNotesIndexConfigurator extends DefaultIndexConfigurator
{
    /**
     * Name of the index.
     *
     * @var string
     */
    protected $name = 'credit_notes_index';
}
