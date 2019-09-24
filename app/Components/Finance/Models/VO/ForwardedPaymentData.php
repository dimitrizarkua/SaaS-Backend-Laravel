<?php

namespace App\Components\Finance\Models\VO;

use App\Core\JsonModel;
use Illuminate\Support\Carbon;

/**
 * Class ForwardedPaymentData
 *
 * @package App\Components\Finance\Models\VO
 */
class ForwardedPaymentData extends JsonModel
{
    /**
     * @var int
     */
    public $srcGLAccountId;

    /**
     * @var int
     */
    public $dstGLAccountId;

    /**
     * @var int User who forwards funds.
     */
    public $userId;

    /**
     * @var array Invoice identifiers.
     */
    public $invoicesIds = [];

    /**
     * @var \Illuminate\Support\Carbon
     */
    public $transferredAt;

    /**
     * @var string
     */
    public $remittanceReference;

    /**
     * @param int $srcGLAccountId
     *
     * @return self
     */
    public function setSourceGLAccountId(int $srcGLAccountId): self
    {
        $this->srcGLAccountId = $srcGLAccountId;

        return $this;
    }

    /**
     * Source GL Account id.
     *
     * @return int
     */
    public function getSourceGLAccountId(): int
    {
        return $this->srcGLAccountId;
    }

    /**
     * @param int $dstGLAccountId
     *
     * @return self
     */
    public function setDestinationGLAccountId(int $dstGLAccountId): self
    {
        $this->dstGLAccountId = $dstGLAccountId;

        return $this;
    }

    /**
     * Destination GL Account id.
     *
     * @return int
     */
    public function getDestinationGLAccountId(): int
    {
        return $this->dstGLAccountId;
    }

    /**
     * @param array $invoicesIds
     *
     * @return self
     */
    public function setInvoicesIds(array $invoicesIds): self
    {
        $this->invoicesIds = $invoicesIds;

        return $this;
    }

    /**
     * Invoices identifiers.
     *
     * @return array
     */
    public function getInvoicesIds(): array
    {
        return $this->invoicesIds;
    }

    /**
     * @param string $transferredAt
     *
     * @return self
     */
    public function setTransferredAt($transferredAt): self
    {
        if (is_string($transferredAt)) {
            $transferredAt = new Carbon($transferredAt);
        }

        $this->transferredAt = $transferredAt;

        return $this;
    }

    /**
     * Gets forwarded payment transferred datetime.
     *
     * @return \Illuminate\Support\Carbon
     */
    public function getTransferredAt(): Carbon
    {
        return $this->transferredAt;
    }

    /**
     * This reference will appear for the franchise the transfer to allocated to and for head office. It is
     * suggested that the user enters the bank’s payment receipt number into this field along with any other
     * information.
     *
     * @param string $remittanceReference
     *
     * @return self
     */
    public function setRemittanceReference(string $remittanceReference): self
    {
        $this->remittanceReference = $remittanceReference;

        return $this;
    }

    /**
     * Returns remittance reference text.
     *
     * @return string
     */
    public function getRemittanceReference(): string
    {
        return $this->remittanceReference;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return \App\Components\Finance\Models\VO\ForwardedPaymentData
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
