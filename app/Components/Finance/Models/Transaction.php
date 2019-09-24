<?php

namespace App\Components\Finance\Models;

use App\Components\Finance\Exceptions\NotAllowedException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use OpenApi\Annotations as OA;

/**
 * Class Transaction
 *
 * @property int                                 $id
 * @property int                                 $accounting_organization_id
 * @property Carbon                              $created_at
 * @property-read Collection|TransactionRecord[] $records
 * @property-read AccountingOrganization         $accountingOrganization
 * @property-read Payment                        $payment
 *
 * @method static Builder|Transaction newModelQuery()
 * @method static Builder|Transaction newQuery()
 * @method static Builder|Transaction query()
 * @method static Builder|Transaction whereAccountingOrganizationId($value)
 * @method static Builder|Transaction whereCreatedAt($value)
 * @method static Builder|Transaction whereId($value)
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "accounting_organization_id",
 *          "created_at",
 *     }
 * )
 */
class Transaction extends Model
{
    protected $guarded = ['id'];
    const UPDATED_AT = null;

    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="accounting_organization_id",
     *    description="Identifier of accounting organization",
     *    type="integer",
     *    example="1"
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * Relationship with transaction_records table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function records(): HasMany
    {
        return $this->hasMany(TransactionRecord::class, 'transaction_id');
    }

    /**
     * Relationship with accounting_organizations table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingOrganization(): BelongsTo
    {
        return $this->belongsTo(AccountingOrganization::class);
    }

    /**
     * Relationship with payments table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'transaction_id', 'id');
    }

    /**
     * Update the model in the database.
     *
     * @param  array $attributes
     * @param  array $options
     *
     * @throws \Exception
     */
    final public function update(array $attributes = [], array $options = [])
    {
        throw  new NotAllowedException('Update operation for this entity isn\'t allowed.');
    }

    /**
     * Delete the model from the database.
     *
     * @throws \Exception
     */
    final public function delete()
    {
        throw  new NotAllowedException('Delete operation for this entity isn\'t allowed.');
    }
}
