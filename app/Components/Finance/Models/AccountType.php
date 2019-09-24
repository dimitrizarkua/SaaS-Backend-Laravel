<?php

namespace App\Components\Finance\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Annotations as OA;

/**
 * Class AccountType
 *
 * @property int                              $id
 * @property string                           $name
 * @property bool                             $increase_action_is_debit
 * @property bool                             $show_on_pl
 * @property bool                             $show_on_bs
 * @property integer                          $account_type_group_id
 * @property-read Collection|GLAccount[]      $glAccounts
 * @property-read Collection|AccountTypeGroup accountTypeGroup
 *
 * @method static Builder|AccountType newModelQuery()
 * @method static Builder|AccountType newQuery()
 * @method static Builder|AccountType query()
 * @method static Builder|AccountType whereId($value)
 * @method static Builder|AccountType whereIncreaseActionIsDebit($value)
 * @method static Builder|AccountType whereName($value)
 * @method static Builder|AccountType whereShowOnBs($value)
 * @method static Builder|AccountType whereShowOnPl($value)
 * @method static Builder|AccountType whereAccountTypeGroupId($value)
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "name",
 *          "increase_action_is_debit",
 *          "show_on_pl",
 *          "show_on_bs",
 *          "account_type_group_id"
 *     }
 * )
 *
 * @mixin \Eloquent
 */
class AccountType extends Model
{
    use ApiRequestFillable;

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
     *    property="name",
     *    description="Account type name",
     *    type="string",
     *    example="Tax payable"
     * ),
     * @OA\Property(
     *    property="increase_action_is_debit",
     *    description="Defines whether action if adding (increasing) money to this account results in debit operation
     * or not (otherwise credit)",
     *    type="boolean",
     * ),
     * @OA\Property(
     *    property="show_on_pl",
     *    description="Defines whether this account should be included in Profit & Loss report",
     *    type="boolean",
     * ),
     * @OA\Property(
     *    property="show_on_bs",
     *    description="Defines whether this account should be included in Balance Sheet report",
     *    type="boolean",
     * ),
     * @OA\Property(
     *    property="account_type_group_id",
     *    description="Defines account type group identifier.",
     *    type="integer",
     *    example=1
     * ),
     */

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function glAccounts(): HasMany
    {
        return $this->hasMany(GLAccount::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountTypeGroup(): BelongsTo
    {
        return $this->belongsTo(AccountTypeGroup::class);
    }
}
