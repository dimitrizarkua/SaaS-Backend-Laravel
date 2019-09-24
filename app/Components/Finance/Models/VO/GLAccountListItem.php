<?php

namespace App\Components\Finance\Models\VO;

use App\Components\Finance\Interfaces\GLAccountListItemInterface;
use App\Components\Finance\Models\GLAccount;
use App\Core\JsonModel;

/**
 * Class GLAccountListItem
 *
 * @package App\Components\Finance\Models\VO
 */
class GLAccountListItem extends JsonModel implements GLAccountListItemInterface
{
    /**
     * @var \App\Components\Finance\Models\GLAccount
     */
    public $glAccount;
    /**
     * @var float
     */
    public $amount;

    public function setGlAccount($glAccount): void
    {
        if (is_int($glAccount)) {
            $glAccount = GLAccount::findOrFail($glAccount);
        }

        $this->glAccount = $glAccount;
    }

    /**
     * GL Account.
     *
     * @return GLAccount
     */
    public function getGlAccount(): GLAccount
    {
        return $this->glAccount;
    }

    /**
     * Amount by which the given account should be increased.
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }
}
