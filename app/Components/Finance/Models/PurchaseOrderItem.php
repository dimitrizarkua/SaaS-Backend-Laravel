<?php

namespace App\Components\Finance\Models;

use App\Components\Models\PositionableMapping;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderItem
 *
 * @package App\Components\Finance\Models
 *
 * @property int                $purchase_order_id
 * @property float              $markup
 * @property string             $created_at
 * @property-read PurchaseOrder $purchaseOrder
 *
 * @OA\Schema(
 *     required={
 *         "id",
 *         "purchase_order_id",
 *         "gs_code_id",
 *         "description",
 *         "unit_cost",
 *         "quantity",
 *         "markup",
 *         "gl_account_id",
 *         "tax_rate_id",
 *         "created_at"
 *     }
 * )
 *
 * @mixin \Eloquent
 */
class PurchaseOrderItem extends FinancialEntityItem
{
    use ApiRequestFillable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Purchase order item identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="purchase_order_id",
     *     description="Identifier of purchase order",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="gs_code_id",
     *     description="Identifier of GS code",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="description",
     *     description="Description",
     *     type="string",
     *     example="It is about PO",
     * ),
     * @OA\Property(
     *     property="unit_cost",
     *     description="Unit cost",
     *     type="number",
     *     format="float",
     *     example=500.15,
     * ),
     * @OA\Property(
     *     property="quantity",
     *     description="Quantity",
     *     type="integer",
     *     example=5,
     * ),
     * @OA\Property(
     *     property="markup",
     *     description="Markup for one unit in percent",
     *     type="number",
     *     format="float",
     *     example=50.55,
     * ),
     * @OA\Property(
     *     property="gl_account_id",
     *     description="GL account identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="tax_rate_id",
     *     description="Tax rate item identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="position",
     *     description="Purchase order item position",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="created_at",
     *     type="string",
     *     format="date-time",
     * ),
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'unit_cost'  => 'float',
        'markup'     => 'float',
    ];

    /**
     * Relationship with purchase_orders table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * Returns amount for the one unit.
     *
     * @return float
     */
    public function getAmountForOneUnit(): float
    {
        $markup = $this->unit_cost * ($this->markup / 100);

        return $this->unit_cost + $markup;
    }

    /**
     * {@inheritDoc}
     */
    public function getPositionableMapping(): PositionableMapping
    {
        return new PositionableMapping($this->purchaseOrder()->getForeignKey());
    }

    /**
     * Query to include total amount of purchase order items with tax.
     *
     * @param string $columnName
     *
     * @return string
     */
    public static function totalAmountIncludeTaxSubQueryString(string $columnName): string
    {
        return sprintf(
            '(SELECT SUM((sub.unit_cost * (1 + (sub.markup / 100)))* (1 + tax_rates.rate) * sub.quantity) AS %s
            FROM (
                SELECT id, unit_cost, quantity, tax_rate_id, markup
                FROM purchase_order_items
                WHERE purchase_order_items.purchase_order_id = purchase_orders.id
            ) AS sub
            LEFT JOIN tax_rates
            ON sub.tax_rate_id = tax_rates.id)',
            $columnName
        );
    }
}
