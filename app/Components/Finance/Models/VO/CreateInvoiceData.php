<?php

namespace App\Components\Finance\Models\VO;

use Illuminate\Support\Carbon;

/**
 * Class CreateInvoiceData
 *
 * @package App\Components\Finance\Models\VO
 */
class CreateInvoiceData extends CreateFinancialEntityData
{
    /**
     * @var \App\Components\Finance\Models\VO\InvoiceItemData[]
     */
    public $items = [];
    /**
     * @var  null|string
     */
    public $reference;
    /**
     * @var \Illuminate\Support\Carbon
     */
    public $due_at;

    public function setDueAt($due_at): self
    {
        if (is_string($due_at)) {
            $due_at = new Carbon($due_at);
        }

        $this->due_at = $due_at;

        return $this;
    }
}
