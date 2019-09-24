<?php

namespace App\Components\Finance\Models\VO;

use App\Core\JsonModel;
use Illuminate\Support\Carbon;

/**
 * Class GLAccountTransactionFilter
 * Defines search filter options for transaction records by gl account id.
 *
 * @package App\Components\Finance\Models\VO
 */
class GLAccountTransactionFilter extends JsonModel
{
    /** @var int */
    public $gl_account_id;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    public $date_from;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    public $date_to;

    /**
     * @return int
     */
    public function getGlAccountId(): int
    {
        return $this->gl_account_id;
    }

    /**
     * @param int $gl_account_id
     *
     * @return \App\Components\Finance\Models\VO\GLAccountTransactionFilter
     */
    public function setGlAccountId(int $gl_account_id): self
    {
        $this->gl_account_id = $gl_account_id;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDateFrom()
    {
        return $this->date_from;
    }

    /**
     * @param null|string $dateFrom
     *
     * @return \App\Components\Finance\Models\VO\GLAccountTransactionFilter
     */
    public function setDateFrom(?string $dateFrom): self
    {
        if (null !== $dateFrom) {
            $this->date_from = new Carbon($dateFrom);
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDateTo()
    {
        return $this->date_to;
    }

    /**
     * @param null|string $dateTo
     *
     * @return \App\Components\Finance\Models\VO\GLAccountTransactionFilter
     */
    public function setDateTo(?string $dateTo): self
    {
        if (null !== $dateTo) {
            $this->date_to = new Carbon($dateTo);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'gl_account_id' => $this->gl_account_id,
            'date_from'     => $this->date_from,
            'date_to'       => $this->date_to,
        ];
    }
}
