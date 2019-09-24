<?php

namespace App\Components\Finance\Models;

use App\Components\Finance\Exceptions\NotAllowedException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use OpenApi\Annotations as OA;

/**
 * Class TransactionRecord
 *
 * @property int              $id
 * @property int              $transaction_id
 * @property int              $gl_account_id
 * @property float            $amount
 * @property bool             $is_debit
 * @property float|null       $balance
 *
 * @property-read GLAccount   $glAccount
 * @property-read Transaction $transaction
 *
 * @method static Builder|TransactionRecord newModelQuery()
 * @method static Builder|TransactionRecord newQuery()
 * @method static Builder|TransactionRecord query()
 * @method static Builder|TransactionRecord whereAmount($value)
 * @method static Builder|TransactionRecord whereGlAccountId($value)
 * @method static Builder|TransactionRecord whereId($value)
 * @method static Builder|TransactionRecord whereIsDebit($value)
 * @method static Builder|TransactionRecord whereTransactionId($value)
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "transaction_id",
 *          "gl_account_id",
 *          "amount",
 *          "is_debit"
 *     }
 * )
 */
class TransactionRecord extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="transaction_id",
     *    description="Identifier of transaction",
     *    type="integer",
     *    example="1"
     * ),
     * @OA\Property(
     *    property="gl_account_id",
     *    description="Identifier of GL Account wich balance was changed",
     *    type="integer",
     *    example=1,
     *    nullable=true,
     * ),
     * @OA\Property(
     *    property="amount",
     *    description="Amount of the record",
     *    type="number",
     *    example=1051.50
     * ),
     * @OA\Property(
     *    property="is_debit",
     *    description="Shows whether is record debit",
     *    type="boolean",
     *    example="false"
     * ),
     */

    /**
     * Relationship with transactions table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Relationship with gl_accounts table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function glAccount()
    {
        return $this->belongsTo(GLAccount::class);
    }

    /**
     * Updates the model in the database.
     *
     * @param  array $attributes
     * @param  array $options
     *
     * @throws NotAllowedException
     */
    final public function update(array $attributes = [], array $options = [])
    {
        throw new NotAllowedException('Update operation for this entity isn\'t allowed.');
    }

    /**
     * Deletes the model from the database.
     *
     * @throws NotAllowedException
     */
    final public function delete()
    {
        throw new NotAllowedException('Delete operation for this entity isn\'t allowed.');
    }

    /**
     * Returns balance by is_debit flag.
     *
     * @param \App\Components\Finance\Models\AccountType $accountType Account type.
     * @param float                                      $initBalance Initial balance for calculation.
     *
     * @return float
     */
    public function getBalance(AccountType $accountType, float $initBalance): float
    {
        return $this->is_debit === $accountType->increase_action_is_debit
            ? $initBalance + $this->amount
            : $initBalance - $this->amount;
    }
}
