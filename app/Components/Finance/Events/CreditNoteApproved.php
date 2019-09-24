<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\CreditNote;
use Illuminate\Queue\SerializesModels;

/**
 * Class CreditNoteApproved
 *
 * @package App\Components\Finance\Events
 */
class CreditNoteApproved
{
    use SerializesModels;

    /**
     * @var CreditNote
     */
    public $creditNote;

    /**
     * CreditNoteApproved constructor.
     *
     * @param \App\Components\Finance\Models\CreditNote $creditNote
     */
    public function __construct(CreditNote $creditNote)
    {
        $this->creditNote = $creditNote;
    }
}
