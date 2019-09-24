<?php

namespace App\Components\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use OpenApi\Annotations as OA;

/**
 * Class Payment
 *
 * @property int                        $id
 * @property string                     $type
 * @property int|null                   $transaction_id
 * @property int|null                   $user_id
 * @property float                      $amount
 * @property string                     $reference
 * @property mixed                      $paid_at
 * @property mixed                      $created_at
 * @property Carbon                     $updated_at
 * @property-read Transaction           $transaction
 * @property-read User                  $user
 * @property-read CreditCardTransaction $creditCardTransaction
 *
 * @method static Builder|Payment newModelQuery()
 * @method static Builder|Payment newQuery()
 * @method static Builder|Payment query()
 * @method static Builder|Payment whereCreatedAt($value)
 * @method static Builder|Payment whereId($value)
 * @method static Builder|Payment wherePaidAt($value)
 * @method static Builder|Payment whereTransactionId($value)
 * @method static Builder|Payment whereType($value)
 * @method static Builder|Payment whereUpdatedAt($value)
 * @method static Builder|Payment whereUserId($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "type",
 *          "amount",
 *          "paid_at",
 *     }
 * )
 *
 * @package App\Components\Finance\Models
 */
class Payment extends Model
{
    protected $guarded = ['id'];

    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *     property="type",
     *     ref="#/components/schemas/PaymentTypes"
     * ),
     * @OA\Property(
     *    property="transaction_id",
     *    description="Identifier of transaction",
     *    type="integer",
     *    example="1"
     * ),
     * @OA\Property(
     *    property="user_id",
     *    description="Identifier of user who created the payment",
     *    type="integer",
     *    example=1,
     *    nullable=true,
     * ),
     * @OA\Property(
     *    property="amount",
     *    description="Payment amount",
     *    type="number",
     *    example=1051.50
     * ),
     * @OA\Property(
     *     property="reference",
     *     description="Payment reference",
     *     type="#3434",
     * ),
     * @OA\Property(
     *    property="paid_at",
     *    description="Date and time of payment",
     *    type="string",
     *    format="date-time",
     *    example="2018-11-10T09:10:11Z"
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'paid_at'    => 'datetime:Y-m-d\TH:i:s\Z',
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'expires_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'amount'     => 'float',
    ];

    /**
     * Relationship with transactions table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'id', 'transaction_id');
    }

    /**
     * Relationship with users table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    /**
     * Relationship with credit card transaction (cc_transactions) table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function creditCardTransaction(): HasOne
    {
        return $this->hasOne(CreditCardTransaction::class, 'payment_id', 'id');
    }
}
