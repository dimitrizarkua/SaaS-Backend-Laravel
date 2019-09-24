<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\CreditNote;
use Illuminate\Queue\SerializesModels;

/**
 * Class CreditNoteItemCreated
 *
 * @package App\Components\Finance\Events
 */
class CreditNoteItemCreated
{
    use SerializesModels;

    /**
     * @var CreditNote
     */
    public $creditNote;

    /**
     * CreditNoteItemCreated constructor.
     *
     * @param \App\Components\Finance\Models\CreditNote     $creditNote
     */
    public function __construct(CreditNote $creditNote)
    {
        $this->creditNote  = $creditNote;
    }
}
