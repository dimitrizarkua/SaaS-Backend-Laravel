<?php

namespace App\Components\Finance\Models\VO;

use App\Core\JsonModel;
use Illuminate\Support\Carbon;
use App\Components\Finance\Interfaces\GLAccountListItemInterface;

/**
 * Class CreatePaymentData
 *
 * @package App\Components\Finance\Models\VO
 */
class CreatePaymentData extends JsonModel
{
    /**
     * @var int|null
     */
    public $userId;
    /**
     * @var float
     */
    public $amount;
    /**
     * @var \Illuminate\Support\Carbon
     */
    public $paidAt;
    /**
     * @var string|null
     */
    public $reference;
    /**
     * @var int
     */
    public $accountingOrganizationId;
    /**
     * @var GLAccountListItem[]
     */
    public $payableGLAccountList = [];
    /**
     * @var GLAccountListItem[]
     */
    public $receivableGLAccountList = [];
    /**
     * @var float
     */
    public $tax = 0;

    /**
     * Returns user id who create a payment.
     *
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Sets user id who create a payment.
     *
     * @param int $userId
     *
     * @return self
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Payment amount.
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Sets payment amount.
     *
     * @param float $amount
     *
     * @return self
     */
    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @param string $paidAt
     *
     * @return self
     */
    public function setPaidAt($paidAt): self
    {
        if (is_string($paidAt)) {
            $paidAt = new Carbon($paidAt);
        }

        $this->paidAt = $paidAt;

        return $this;
    }

    /**
     * Datetime when payment was paid.
     *
     * @return \Illuminate\Support\Carbon
     */
    public function getPaidAt(): Carbon
    {
        return $this->paidAt;
    }

    /**
     * Accounting organization id to which the payment belongs.
     *
     * @return int
     */
    public function getAccountingOrganizationId(): int
    {
        return $this->accountingOrganizationId;
    }

    /**
     * @param int $accountingOrganizationId Accounting organization id to which the payment belongs.
     *
     * @return self
     */
    public function setAccountingOrganizationId(int $accountingOrganizationId): self
    {
        $this->accountingOrganizationId = $accountingOrganizationId;

        return $this;
    }

    /**
     * GL Accounts list which should be decreased for the amount.
     *
     * @return GLAccountListItemInterface[]
     */
    public function getPayableGLAccountsList(): array
    {
        return $this->payableGLAccountList;
    }

    /**
     * GL Accounts list which should be increased for the amount.
     *
     * @return GLAccountListItemInterface[]
     */
    public function getReceivableGLAccountsList(): array
    {
        return $this->receivableGLAccountList;
    }

    /**
     * Returns reference.
     *
     * @return string|null
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @param string|null $reference
     *
     * @return self
     */
    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }
}
