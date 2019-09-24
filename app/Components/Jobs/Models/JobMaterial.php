<?php

namespace App\Components\Jobs\Models;

use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\UsageAndActuals\Models\InsurerContractMaterial;
use App\Components\UsageAndActuals\Models\Material;
use App\Models\DateTimeFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class JobMaterial
 *
 * @property int              $id
 * @property int              $job_id
 * @property int              $material_id
 * @property int|null         $creator_id
 * @property Carbon           $used_at
 * @property float|null       $sell_cost_per_unit
 * @property float            $buy_cost_per_unit
 * @property int              $quantity_used
 * @property int|null         $quantity_used_override
 * @property int|null         $invoice_item_id
 * @property Carbon           $created_at
 * @property Carbon           $updated_at
 * @property Carbon|null      $deleted_at
 *
 * @property-read Job         $job
 * @property-read Material    $material
 * @property-read User        $user
 * @property-read InvoiceItem $invoiceItem
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     type="object",
 *     required={
 *         "id",
 *         "job_id",
 *         "material_id",
 *         "used_at",
 *         "buy_cost_per_unit",
 *         "quantity_used",
 *     }
 * )
 *
 * @package App\Components\Jobs\Models
 */
class JobMaterial extends Model
{
    use DateTimeFillable;
    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="job_id",
     *    description="Job identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="material_id",
     *    description="Material identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="creator_id",
     *    description="User identifier",
     *    type="integer",
     *    nullable=true,
     *    example=1,
     * ),
     * @OA\Property(property="used_at", type="string", format="date-time"),
     * @OA\Property(
     *    property="sell_cost_per_unit",
     *    description="Sell cost per unit",
     *    type="number",
     *    nullable=true,
     *    example=12.3,
     * ),
     * @OA\Property(
     *    property="buy_cost_per_unit",
     *    description="Buy cost per unit",
     *    type="number",
     *    format="float",
     *    example=12.3
     * ),
     * @OA\Property(
     *    property="quantity_used",
     *    description="Units quantity",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="quantity_used_override",
     *    description="Overridden units quantity",
     *    type="integer",
     *    nullable=true,
     *    example=1
     * ),
     * @OA\Property(
     *    property="invoice_item_id",
     *    description="Invoice item identifier",
     *    type="integer",
     *    nullable=true,
     *    example=1,
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     * @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     */

    public $timestamps = true;

    protected $table = 'job_material';

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at'         => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'         => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at'         => 'datetime:Y-m-d\TH:i:s\Z',
        'used_at'            => 'datetime:Y-m-d\TH:i:s\Z',
        'sell_cost_per_unit' => 'float',
        'buy_cost_per_unit'  => 'float',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'used_at',
    ];

    /**
     * Relationship with jobs table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    /**
     * Relationship with materials table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    /**
     * Relationship with users table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Relationship with invoice_items table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    /**
     * Returns Invoice that uses this material if exists.
     *
     * @return \App\Components\Finance\Models\Invoice|null
     */
    public function getInvoice(): ?Invoice
    {
        return $this->invoice_item_id ? $this->invoiceItem->invoice : null;
    }

    /**
     * Returns sell cost for current job material based on insurer contract.
     *
     * @return float
     */
    public function calculateSellCost(): float
    {
        $insurerContractMaterial = InsurerContractMaterial::query()
            ->where('insurer_contract_id', $this->job->insurer_contract_id)
            ->where('material_id', $this->material_id)
            ->first();

        return $insurerContractMaterial
            ? $insurerContractMaterial->sell_cost_per_unit
            : $this->material->default_sell_cost_per_unit;
    }

    /**
     * Returns buy cost for current job material based on material.
     *
     * @return float
     */
    public function calculateBuyCost(): float
    {
        return $this->material->default_buy_cost_per_unit;
    }

    /**
     * Returns amount of job material without taxes.
     *
     * @return float
     */
    public function totalAmount(): float
    {
        return (float)bcmul(
            (string)$this->sell_cost_per_unit,
            (string)$this->quantity_used,
            2
        );
    }

    /**
     * Returns overridden amount of job material without taxes.
     *
     * @return float
     */
    public function totalAmountOverride(): float
    {
        return (float)bcmul(
            (string)$this->sell_cost_per_unit,
            (string)$this->quantity_used_override,
            2
        );
    }

    /**
     * Setter for used_at attribute.
     *
     * @param string|Carbon $datetime
     *
     * @return self
     *
     * @throws \Throwable
     */
    public function setUsedAtAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('used_at', $datetime);
    }
}
