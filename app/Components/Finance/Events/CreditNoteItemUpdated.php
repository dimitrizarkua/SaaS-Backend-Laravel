<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteItem;
use Illuminate\Queue\SerializesModels;

/**
 * Class CreditNoteItemUpdated
 *
 * @package App\Components\Finance\Events
 */
class CreditNoteItemUpdated
{
    use SerializesModels;

    /**
     * @var CreditNote
     */
    public $creditNote;

    /**
     * CreditNoteItemUpdated constructor.
     *
     * @param \App\Components\Finance\Models\CreditNote     $creditNote
     */
    public function __construct(CreditNote $creditNote)
    {
        $this->creditNote  = $creditNote;
    }
}
