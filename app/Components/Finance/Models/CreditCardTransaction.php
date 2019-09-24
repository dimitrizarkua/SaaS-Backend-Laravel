<?php

namespace App\Components\Finance\Models;

use App\Components\Finance\Exceptions\NotAllowedException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class CreditCardTransaction
 *
 * @property int    $payment_id
 * @property float  $amount
 * @property string $external_transaction_id
 * @property Carbon $created_at
 * @property Carbon $settled_at
 *
 * @method static Builder|Transaction newModelQuery()
 * @method static Builder|Transaction newQuery()
 * @method static Builder|Transaction query()
 * @method static Builder|Transaction wherePaymentId($value)
 * @method static Builder|Transaction whereAmount($value)
 * @method static Builder|Transaction whereExternalTransactionId($value)
 * @method static Builder|Transaction whereCreatedAt($value)
 * @method static Builder|Transaction whereSettledAt($value)
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *          "payment_id",
 *          "amount",
 *          "external_transaction_id",
 *          "settled_at",
 *          "created_at"
 *     }
 * )
 */
class CreditCardTransaction extends Model
{
    const UPDATED_AT = null;

    public $timestamps = true;

    public $primaryKey = 'payment_id';

    protected static $unguarded = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cc_transactions';

    /**
     * @OA\Property(
     *    property="payment_id",
     *    description="Payment identifier.",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="amount",
     *    description="Payment amount.",
     *    type="number",
     *    example="5.33"
     * ),
     * @OA\Property(
     *    property="external_transaction_id",
     *    description="Transaction identifier from payment gateway.",
     *    type="string",
     *    example="ch_p9kb9f4m_BQDGIjn1LboMg"
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="settled_at", type="string", format="date-time"),
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'settled_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'amount'     => 'float',
    ];

    /**
     * Relationship with payments table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'id', 'payment_id');
    }

    /**
     * Update the model in the database.
     *
     * @param array $attributes
     * @param array $options
     *
     * @throws \Exception
     */
    final public function update(array $attributes = [], array $options = [])
    {
        throw new NotAllowedException('Update operation for this entity isn\'t allowed.');
    }

    /**
     * Delete the model from the database.
     *
     * @throws \Exception
     */
    final public function delete()
    {
        throw new NotAllowedException('Delete operation for this entity isn\'t allowed.');
    }
}
