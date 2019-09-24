<?php

namespace App\Components\Finance\Models;

use App\Components\Models\PositionableMapping;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class InvoiceItem
 *
 * @property int          $invoice_id
 * @property-read Invoice $invoice
 * @method static Builder withTotalAmountExcludeTax($columnName)
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "invoice_id",
 *          "gs_code_id",
 *          "description",
 *          "unit_cost",
 *          "quantity",
 *          "discount",
 *          "gl_account_id",
 *          "tax_rate_id"
 *     }
 * )
 *
 * @package App\Components\Finance\Models
 * @mixin \Eloquent
 */
class InvoiceItem extends FinancialEntityItem
{
    use ApiRequestFillable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Model identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="invoice_id",
     *     description="Invoice identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="gs_code_id",
     *     description="Item GS code identifier",
     *     type="integer",
     *     example=2,
     * ),
     * @OA\Property(
     *     property="description",
     *     description="Description",
     *     type="string",
     *     example="General Restoration Technician labour (hours)",
     * ),
     * @OA\Property(
     *     property="unit_cost",
     *     description="Cost of one unit",
     *     type="number",
     *     format="float",
     *     example=58.00,
     * ),
     * @OA\Property(
     *     property="quantity",
     *     description="Quantity of units in the invoice item",
     *     type="integer",
     *     example=5,
     * ),
     * @OA\Property(
     *     property="discount",
     *     description="Discount for one unit in percent",
     *     type="number",
     *     format="float",
     *     example=35.88,
     * ),
     * @OA\Property(
     *     property="gl_account_id",
     *     description="GL Account identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="tax_rate_id",
     *     description="Tax Rate identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="position",
     *     description="Invoice item position",
     *     type="integer",
     *     example=1,
     * ),
     */

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'unit_cost'  => 'float',
        'discount'   => 'float',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Returns amount for the one unit.
     *
     * @return float
     */
    public function getAmountForOneUnit(): float
    {
        $discount = $this->unit_cost * ($this->discount / 100);

        return $this->unit_cost - $discount;
    }

    /**
     * {@inheritDoc}
     */
    public function getPositionableMapping(): PositionableMapping
    {
        return new PositionableMapping($this->invoice()->getForeignKey());
    }

    /**
     * Scope a query to include total amount of invoice items without tax.
     *
     * @param Builder $query
     * @param string  $columnName
     *
     * @return Builder
     */
    public function scopeWithTotalAmountExcludeTax(Builder $query, string $columnName): Builder
    {
        $amountExTaxQuery = sprintf(
            'SUM((invoice_items.unit_cost * (1 - (invoice_items.discount / 100))) * invoice_items.quantity) as %s',
            $columnName
        );

        return $query->selectRaw($amountExTaxQuery);
    }

    /**
     * Query to include total amount of invoice items with tax.
     *
     * @param string $columnName
     *
     * @return string
     */
    public static function totalAmountIncludeTaxSubQueryString(string $columnName): string
    {
        return sprintf(
            '(SELECT SUM(
                (sub.unit_cost * (1 - (sub.discount / 100))) 
                * (1 + CASE WHEN tax_rates.rate > 0 THEN tax_rates.rate ELSE 0 END)
                * sub.quantity
            ) AS %s
            FROM (
                SELECT id, unit_cost, quantity, discount, tax_rate_id
                FROM invoice_items
                WHERE invoice_items.invoice_id = invoices.id
            ) AS sub
            LEFT JOIN tax_rates
            ON sub.tax_rate_id = tax_rates.id)',
            $columnName
        );
    }
}
