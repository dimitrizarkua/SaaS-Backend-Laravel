<?php

namespace App\Components\Jobs\Models\VO;

use App\Components\Finance\Models\InvoiceItem;
use App\Core\JsonModel;
use Illuminate\Support\Carbon;

/**
 * Class JobMaterialData
 *
 * @property int      $id
 * @property int      $job_id
 * @property int      $material_id
 * @property int|null $creator_id
 * @property Carbon   $used_at
 * @property int      $quantity_used
 * @property int|null $quantity_used_override
 * @property int|null $invoice_item_id
 *
 * @package App\Components\Jobs\VO
 */
class JobMaterialData extends JsonModel
{
    /**
     * @var int
     */
    public $job_id;
    /**
     * @var int
     */
    public $material_id;
    /**
     * @var int|null
     */
    public $creator_id;
    /**
     * @var \Illuminate\Support\Carbon
     */
    public $used_at;
    /**
     * @var int
     */
    public $quantity_used;
    /**
     * @var int|null
     */
    public $quantity_used_override;
    /**
     * @var int|null
     */
    public $invoice_item_id;

    /**
     * @param string $used_at
     *
     * @return self
     */
    public function setUsedAt(?string $used_at): self
    {
        if (null !== $used_at) {
            $this->used_at = new Carbon($used_at);
        } else {
            $this->used_at = null;
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
     * JobMaterialData constructor.
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
