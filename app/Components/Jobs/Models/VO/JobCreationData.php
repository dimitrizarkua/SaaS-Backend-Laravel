<?php

namespace App\Components\Jobs\Models\VO;

use App\Core\JsonModel;
use Illuminate\Support\Carbon;

/**
 * Class JobCreationData
 *
 * @package App\Components\Jobs\Models\VO
 */
class JobCreationData extends JsonModel
{
    /**
     * @var string|null;
     */
    private $claim_number;
    /**
     * @var int|null
     */
    public $job_service_id;
    /**
     * @var int|null
     */
    public $insurer_id;
    /**
     * @var int|null
     */
    public $site_address_id;
    /**
     * @var float|null
     */
    public $site_address_lat;
    /**
     * @var float|null
     */
    public $site_address_lng;
    /**
     * @var int|null
     */
    public $assigned_location_id;
    /**
     * @var int|null
     */
    public $owner_location_id;
    /**
     * @var string|null
     */
    public $reference_number;
    /**
     * @var string|null
     */
    public $claim_type;
    /**
     * @var string|null
     */
    public $criticality;
    /**
     * @var \Illuminate\Support\Carbon|null
     */
    public $date_of_loss;
    /**
     * @var \Illuminate\Support\Carbon|null
     */
    public $initial_contact_at;
    /**
     * @var string|null
     */
    public $cause_of_loss;
    /**
     * @var string|null
     */
    public $description;
    /**
     * @var float|null
     */
    public $anticipated_revenue;
    /**
     * @var  \Illuminate\Support\Carbon|null
     */
    public $anticipated_invoice_date;
    /**
     * @var \Illuminate\Support\Carbon|null
     */
    public $authority_received_at;
    /**
     * @var float|null
     */
    public $expected_excess_payment;

    /**
     * @var integer|null
     */
    public $recurring_job_id;

    /**
     * @param null|string $date_of_loss
     */
    public function setDateOfLoss(?string $date_of_loss): void
    {
        if (null !== $date_of_loss) {
            $this->date_of_loss = new Carbon($date_of_loss);
        }
    }

    /**
     * @param string|null $initial_contact_at
     */
    public function setInitialContactAt(?string $initial_contact_at): void
    {
        if (null !== $initial_contact_at) {
            $this->initial_contact_at = new Carbon($initial_contact_at);
        }
    }

    /**
     * @param string|null $anticipated_invoice_date
     */
    public function setAnticipatedInvoiceDate(?string $anticipated_invoice_date): void
    {
        if (null !== $anticipated_invoice_date) {
            $this->anticipated_invoice_date = new Carbon($anticipated_invoice_date);
        }
    }

    /**
     * @param null|string $authority_received_at
     */
    public function setAuthorityReceivedAt(?string $authority_received_at): void
    {
        if (null !== $authority_received_at) {
            $this->authority_received_at = new Carbon($authority_received_at);
        }
    }

    /**
     * @param null|integer $recurring_job_id
     *
     * @return \App\Components\Jobs\Models\VO\JobCreationData
     */
    public function setRecurringJobId(?int $recurring_job_id): self
    {
        $this->recurring_job_id = $recurring_job_id;

        return $this;
    }

    /**
     * @param null|string $claim_number
     */
    public function setClaimNumber(?string $claim_number): void
    {
        $this->claim_number = $claim_number;
    }

    /**
     * Returns claim number
     *
     * @return null|string
     */
    public function getClaimNumber(): ?string
    {
        return $this->claim_number;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result                 = parent::toArray();
        $result['claim_number'] = $this->getClaimNumber();

        return $result;
    }

    /**
     * Modifies the data according to duplicate job logic.
     *
     * @return JobCreationData
     */
    public function duplicate(): self
    {
        $this->claim_number = null;

        return $this;
    }
}
