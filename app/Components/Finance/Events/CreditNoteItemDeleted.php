<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\CreditNote;
use Illuminate\Queue\SerializesModels;

/**
 * Class CreditNoteItemDeleted
 *
 * @package App\Components\Finance\Events
 */
class CreditNoteItemDeleted
{
    use SerializesModels;

    /**
     * @var CreditNote
     */
    public $creditNote;

    /**
     * CreditNoteItemDeleted constructor.
     *
     * @param \App\Components\Finance\Models\CreditNote $creditNote
     */
    public function __construct(CreditNote $creditNote)
    {
        $this->creditNote  = $creditNote;
    }
}
