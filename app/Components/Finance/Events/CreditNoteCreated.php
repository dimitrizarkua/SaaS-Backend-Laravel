<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\CreditNote;
use Illuminate\Queue\SerializesModels;

/**
 * Class CreditNoteCreated
 *
 * @package App\Components\Finance\Events
 */
class CreditNoteCreated
{
    use SerializesModels;

    /**
     *
     * @var CreditNote
     */
    public $creditNote;

    /**
     * CreditNoteCreated constructor.
     *
     * @param CreditNote $creditNote
     */
    public function __construct(CreditNote $creditNote)
    {
        $this->creditNote = $creditNote;
    }
}
