<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\CreditNote;
use Illuminate\Queue\SerializesModels;

/**
 * Class CreditNoteUpdated
 *
 * @package App\Components\Finance\Events
 */
class CreditNoteUpdated
{
    use SerializesModels;

    /**
     * @var CreditNote
     */
    public $creditNote;

    /**
     * CreditNoteUpdated constructor.
     *
     * @param \App\Components\Finance\Models\CreditNote $creditNote
     */
    public function __construct(CreditNote $creditNote)
    {
        $this->creditNote = $creditNote;
    }
}
