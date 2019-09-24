<?php

namespace App\Components\Finance\Models;

use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class ForwardedPaymentInvoice
 *
 * @property int                                                  $forwarded_payment_id
 * @property int                                                  $invoice_id
 * @property float                                                $amount
 *
 * @property-read \App\Components\Finance\Models\Invoice          $invoice
 * @property-read \App\Components\Finance\Models\ForwardedPayment $forwardedPayment
 *
 * @method static Builder|ForwardedPaymentInvoice newModelQuery()
 * @method static Builder|ForwardedPaymentInvoice newQuery()
 * @method static Builder|ForwardedPaymentInvoice query()
 * @method static Builder|ForwardedPaymentInvoice whereForwardedPaymentId($value)
 * @method static Builder|ForwardedPaymentInvoice whereInvoiceId($value)
 * @method static Builder|ForwardedPaymentInvoice whereAmount($value)
 *
 * @OA\Schema(
 *     required={
 *          "invoice_id",
 *          "forwarded_payment_id",
 *          "amount"
 *     }
 * )
 *
 * @mixin \Eloquent
 */
class ForwardedPaymentInvoice extends Model
{
    use HasCompositePrimaryKey;

    public $timestamps   = false;
    public $incrementing = false;

    protected $table      = 'forwarded_payment_invoice';
    protected $fillable   = ['invoice_id', 'forwarded_payment_id'];
    protected $primaryKey = ['invoice_id', 'forwarded_payment_id'];

    /**
     * @OA\Property(
     *    property="invoice_id",
     *    description="Invoice identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="forwarded_payment_id",
     *    description="Forwarded payment identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="amount",
     *    description="Invoice payment amount",
     *    type="number",
     *    example="1.11"
     * )
     */

    /**
     * Defines relation to invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Defines relation to forwarded payment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forwardedPayment(): BelongsTo
    {
        return $this->belongsTo(ForwardedPayment::class, 'forwarded_payment_id');
    }
}
