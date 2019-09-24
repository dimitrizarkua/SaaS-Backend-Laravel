<?php

namespace App\Components\Finance\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class ForwardedPayment
 *
 * @property int          $id
 * @property int          $payment_id
 * @property string       $remittance_reference
 * @property Carbon       $transferred_at
 *
 * @property-read Payment $payment
 * @property-read Invoice $invoice
 *
 * @method static Builder|ForwardedPayment newModelQuery()
 * @method static Builder|ForwardedPayment newQuery()
 * @method static Builder|ForwardedPayment query()
 * @method static Builder|ForwardedPayment wherePaymentId($value)
 * @method static Builder|ForwardedPayment whereRemittanceReference($value)
 * @method static Builder|ForwardedPayment whereTransferredAt($value)
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "payment_id",
 *          "transferred_at",
 *     }
 * )
 *
 * @mixin \Eloquent
 */
class ForwardedPayment extends Model
{
    use ApiRequestFillable;

    public $timestamps = false;

    protected $table   = 'forwarded_payments';
    protected $guarded = ['id'];

    protected $casts = [
        'transferred_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="payment_id",
     *    description="Payment identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="Remittance reference",
     *    description="Remittance reference text",
     *    type="string",
     *    example="Text"
     * ),
     * @OA\Property(
     *    property="transferred_at",
     *    description="Date to transfer",
     *    type="string",
     *    format="date-time",
     *    example="2018-11-10T09:10:11Z",
     * )
     */

    /**
     * Defines relation to payment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * Defines relation to forwarded payment invoice table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function invoice(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Invoice::class,
                'forwarded_payment_invoice',
                'forwarded_payment_id',
                'invoice_id'
            )->withPivot('amount');
    }
}
