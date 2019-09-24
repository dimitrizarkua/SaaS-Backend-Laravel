<?php

namespace App\Components\Finance\Models;

use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class InvoicePayment
 *
 * @property int          $payment_id Payment identifier.
 * @property int          $invoice_id Invoice identifier.
 * @property float        $amount     Invoice payment amount.
 * @property boolean      $is_fp      Indicates whether invoice payment marked as forwarded.
 * @property-read Invoice $invoice    Assigned invoice
 *
 * @OA\Schema(
 *     required={
 *          "payment_id",
 *          "invoice_id",
 *          "amount",
 *          "is_fp"
 *     }
 * )
 *
 * @package App\Components\Finance\Models
 * @mixin \Eloquent
 */
class InvoicePayment extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @OA\Property(
     *    property="payment_id",
     *    description="Payment identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="invoice_id",
     *    description="Invoice identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="amount",
     *    description="Amount of the invoice",
     *    type="number",
     *    format="float",
     *    example=2.22,
     * ),
     * @OA\Property(
     *    property="is_fp",
     *    description="Indicates whether invoice marked as forwarded.",
     *    type="boolean",
     *    default="false",
     *    example=true,
     * ),
     */

    public $incrementing = false;
    public $fillable     = ['payment_id', 'invoice_id', 'amount', 'is_fp'];
    public $primaryKey   = ['payment_id', 'invoice_id'];
    public $timestamps   = false;

    protected $table = 'invoice_payment';

    protected $casts = [
        'is_fp'  => 'boolean',
        'amount' => 'float',
    ];

    /**
     * Associated invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Query to include total paid amount of invoice.
     *
     * @param string $columnName
     *
     * @return string
     */
    public static function totalPaidAmountSubQueryString(string $columnName): string
    {
        return sprintf(
            '(SELECT SUM(amount) as %s
            FROM invoice_payment
            WHERE invoice_payment.invoice_id = invoices.id)',
            $columnName
        );
    }
}
