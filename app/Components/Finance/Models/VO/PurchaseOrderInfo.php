<?php

namespace App\Components\Finance\Models\VO;

use App\Components\Finance\Interfaces\PurchaseOrderInfoInterface;
use App\Core\JsonModel;

/**
 * Class PurchaseOrderInfo
 *
 * @package App\Components\Finance\Models\VO
 * @method static PurchaseOrderInfo createFromJson($data = null, $target = null, bool $exceptionOnMissingData = true)
 */
class PurchaseOrderInfo extends JsonModel implements PurchaseOrderInfoInterface
{
    /**
     * @var \App\Components\Finance\Models\VO\CounterItem
     */
    private $draftCounter;

    /**
     * @var \App\Components\Finance\Models\VO\CounterItem
     */
    private $pendingApprovalCounter;

    /**
     * @var \App\Components\Finance\Models\VO\CounterItem
     */
    private $approvedCounter;

    /**
     * {@inheritdoc}
     */
    public function getDraftCounter(): CounterItem
    {
        return $this->draftCounter;
    }

    /**
     * Setter for draft data.
     *
     * @param \App\Components\Finance\Models\VO\CounterItem $draftData
     *
     * @return \App\Components\Finance\Models\VO\PurchaseOrderInfo
     */
    public function setDraftCounter(CounterItem $draftData): self
    {
        $this->draftCounter = $draftData;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingApprovalCounter(): CounterItem
    {
        return $this->pendingApprovalCounter;
    }

    /**
     * Setter for pending approval data.
     *
     * @param \App\Components\Finance\Models\VO\CounterItem $pendingApproval
     *
     * @return \App\Components\Finance\Models\VO\PurchaseOrderInfo
     */
    public function setPendingApprovalCounter(CounterItem $pendingApproval): self
    {
        $this->pendingApprovalCounter = $pendingApproval;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getApprovedCounter(): CounterItem
    {
        return $this->approvedCounter;
    }

    /**
     * Setter for approved data.
     *
     * @param \App\Components\Finance\Models\VO\CounterItem $approved
     *
     * @return \App\Components\Finance\Models\VO\PurchaseOrderInfo
     */
    public function setApprovedCounter(CounterItem $approved): self
    {
        $this->approvedCounter = $approved;

        return $this;
    }
}
