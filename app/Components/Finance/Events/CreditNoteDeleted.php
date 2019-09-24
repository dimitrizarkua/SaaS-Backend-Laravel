<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\CreditNote;
use Illuminate\Queue\SerializesModels;

/**
 * Class CreditNoteDeleted
 *
 * @package App\Components\Finance\Events
 */
class CreditNoteDeleted
{
    use SerializesModels;

    /**
     * @var CreditNote
     */
    public $creditNote;

    /**
     * CreditNoteDeleted constructor.
     *
     * @param CreditNote $creditNote
     */
    public function __construct(CreditNote $creditNote)
    {
        $this->creditNote = $creditNote;
    }
}
