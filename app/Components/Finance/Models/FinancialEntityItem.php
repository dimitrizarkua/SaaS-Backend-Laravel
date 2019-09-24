<?php

namespace App\Components\Finance\Models;

use App\Contracts\PositionableInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class FinancialEntityItem
 *
 * @property int            $id
 * @property int            $gs_code_id
 * @property string         $description
 * @property float          $unit_cost
 * @property int            $quantity
 * @property float          $discount
 * @property int            $gl_account_id
 * @property int            $tax_rate_id
 * @property int            $position
 *
 * @property-read GLAccount $glAccount
 * @property-read GSCode    $gsCode
 * @property-read TaxRate   $taxRate
 *
 * @package App\Components\Finance\Models
 */
abstract class FinancialEntityItem extends Model implements PositionableInterface
{
    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'position' => 1,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gsCode(): BelongsTo
    {
        return $this->belongsTo(GSCode::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function glAccount(): BelongsTo
    {
        return $this->belongsTo(GLAccount::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    /**
     * Returns amount for the one unit.
     *
     * @return float
     */
    abstract public function getAmountForOneUnit(): float;

    /**
     * Returns item amount (without taxes).
     *
     * @return float
     */
    public function getSubTotal(): float
    {
        return $this->getAmountForOneUnit() * $this->quantity;
    }

    /**
     * Returns item total amount (include taxes).
     *
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->getSubTotal() + $this->getItemTax();
    }

    /**
     * Returns tax for item.
     *
     * @return float
     */
    public function getItemTax(): float
    {
        return $this->getSubTotal() * $this->taxRate->rate;
    }
}
