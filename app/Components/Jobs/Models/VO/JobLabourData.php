<?php

namespace App\Components\Jobs\Models\VO;

use App\Components\Finance\Models\InvoiceItem;
use App\Core\JsonModel;
use Illuminate\Support\Carbon;

/**
 * Class JobLabourData
 *
 * @property int      $job_id
 * @property int      $labour_type_id
 * @property int      $worker_id
 * @property int      $creator_id
 * @property Carbon   $started_at
 * @property Carbon   $ended_at
 * @property Carbon   $started_at_override
 * @property Carbon   $ended_at_override
 * @property int      $break
 * @property int|null $invoice_item_id
 *
 * @package App\Components\Jobs\Models\VO
 */
class JobLabourData extends JsonModel
{
    /**
     * @var int
     */
    public $job_id;
    /**
     * @var int
     */
    public $labour_type_id;
    /**
     * @var int|null
     */
    public $worker_id;
    /**
     * @var int|null
     */
    public $creator_id;
    /**
     * @var \Illuminate\Support\Carbon
     */
    public $started_at;
    /**
     * @var \Illuminate\Support\Carbon
     */
    public $ended_at;
    /**
     * @var \Illuminate\Support\Carbon
     */
    public $started_at_override;
    /**
     * @var \Illuminate\Support\Carbon
     */
    public $ended_at_override;
    /**
     * @var int
     */
    public $break;
    /**
     * @var int|null
     */
    public $invoice_item_id;

    /**
     * @param string $started_at
     *
     * @return self
     */
    public function setStartedAt(?string $started_at): self
    {
        if (null !== $started_at) {
            $this->started_at = new Carbon($started_at);
        } else {
            $this->started_at = null;
        }

        return $this;
    }

    /**
     * @param string $ended_at
     *
     * @return self
     */
    public function setEndedAt(?string $ended_at): self
    {
        if (null !== $ended_at) {
            $this->ended_at = new Carbon($ended_at);
        } else {
            $this->ended_at = null;
        }

        return $this;
    }

    /**
     * @param string $started_at_override
     *
     * @return self
     */
    public function setStartedAtOverride(?string $started_at_override): self
    {
        if (null !== $started_at_override) {
            $this->started_at_override = new Carbon($started_at_override);
        } else {
            $this->started_at_override = null;
        }

        return $this;
    }

    /**
     * @param string $ended_at_override
     *
     * @return self
     */
    public function setEndedAtOverride(?string $ended_at_override): self
    {
        if (null !== $ended_at_override) {
            $this->ended_at_override = new Carbon($ended_at_override);
        } else {
            $this->ended_at_override = null;
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function invoiceJobId()
    {
        return $this->invoice_item_id
            ? InvoiceItem::find($this->invoice_item_id)->invoice->job_id
            : null;
    }

    /**
     * JobLabourData constructor.
     *
     * @param array|null $properties
     *
     * @throws \JsonMapper_Exception
     */
    public function __construct(?array $properties = null)
    {
        $hidden       = array_diff_key(get_class_vars(self::class), $properties);
        $this->hidden = array_keys($hidden);
        parent::__construct($properties);
    }
}
