<?php

namespace App\Components\Finance\Models;

use App\Components\Finance\Models\Filters\GLAccountFilter;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

/**
 * Class GLAccount
 *
 * @property int                                 $id
 * @property int                                 $accounting_organization_id
 * @property int                                 $account_type_id
 * @property int|null                            $tax_rate_id
 * @property string                              $code
 * @property string                              $name
 * @property string|null                         $description
 * @property string|null                         $bank_account_name
 * @property string|null                         $bank_account_number
 * @property string|null                         $bank_bsb
 * @property bool                                $enable_payments_to_account
 * @property string                              $status
 * @property bool                                $is_active
 * @property-read AccountType                    $accountType
 * @property-read AccountingOrganization         $accountingOrganization
 * @property-read TaxRate                        $taxRate
 * @property-read Collection|TransactionRecord[] $transactionRecords
 *
 * @method static Builder|GLAccount newModelQuery()
 * @method static Builder|GLAccount newQuery()
 * @method static Builder|GLAccount query()
 * @method static Builder|GLAccount whereAccountTypeId($value)
 * @method static Builder|GLAccount whereAccountingOrganizationId($value)
 * @method static Builder|GLAccount whereBankAccountNumber($value)
 * @method static Builder|GLAccount whereBankBsb($value)
 * @method static Builder|GLAccount whereCode($value)
 * @method static Builder|GLAccount whereDescription($value)
 * @method static Builder|GLAccount whereEnablePaymentsToAccount($value)
 * @method static Builder|GLAccount whereId($value)
 * @method static Builder|GLAccount whereIsActive($value)
 * @method static Builder|GLAccount whereName($value)
 * @method static Builder|GLAccount whereStatus($value)
 * @method static Builder|GLAccount whereTaxRateId($value)
 * @method static Builder|GLAccount withCode($code, $accountingOrganizationId)
 * @method static Builder|GLAccount withBankAccount($accountingOrganizationId)
 * @method static Builder|GLAccount byAccountTypeGroupName($accountTypeGroupName, $glAccountId)
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "accounting_organization_id",
 *          "account_type_id",
 *          "code",
 *          "name",
 *          "enable_payments_to_account",
 *          "status",
 *          "is_active"
 *     }
 * )
 *
 * @mixin \Eloquent
 */
class GLAccount extends Model
{
    use ApiRequestFillable;

    public const CLEARING_ACCOUNT_CODE = '612';

    public const FRANCHISE_PAYMENTS_ACCOUNT_CODE = '614';

    public const TRADING_BANK_ACCOUNT_NAME = 'Trading Bank Account';

    public $timestamps = false;

    protected $table   = 'gl_accounts';
    protected $guarded = ['id'];

    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="accounting_organization_id",
     *    description="Identifier of related accounting organization",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="account_type_id",
     *    description="Identifier of account type",
     *    type="integer",
     *    example="1"
     * ),
     * @OA\Property(
     *    property="tax_rate_id",
     *    description="Identifier of Tax Rate",
     *    type="integer",
     *    example="1",
     *    nullable=true,
     * ),
     * @OA\Property(
     *    property="code",
     *    description="Account code",
     *    type="string",
     *    example="CODE"
     * ),
     * @OA\Property(
     *    property="name",
     *    description="Account name",
     *    type="string",
     *    example="Tax payable"
     * ),
     * @OA\Property(
     *    property="description",
     *    description="Account description",
     *    type="string",
     *    example="Some description",
     *    nullable=true,
     * ),
     * @OA\Property(
     *    property="bank_account_name",
     *    description="Bank account name",
     *    type="string",
     *    example="Account name",
     *    nullable=true,
     * ),
     * @OA\Property(
     *    property="bank_account_number",
     *    description="Bank account number",
     *    type="string",
     *    example="03-678",
     *    nullable=true,
     * ),
     * @OA\Property(
     *    property="bank_bsb",
     *    description="Bank BSB number",
     *    type="string",
     *    example="03-678",
     *    nullable=true,
     * ),
     * @OA\Property(
     *    property="enable_payments_to_account",
     *    description="Is payments enabled for the account",
     *    type="boolean",
     * ),
     * @OA\Property(
     *    property="status",
     *    description="Account status",
     *    type="string",
     *    example="some status",
     * ),
     * @OA\Property(
     *    property="is_active",
     *    description="Defines whether is this account active",
     *    type="boolean",
     * ),
     */

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingOrganization(): BelongsTo
    {
        return $this->belongsTo(AccountingOrganization::class, 'accounting_organization_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactionRecords(): HasMany
    {
        return $this->hasMany(TransactionRecord::class, 'gl_account_id');
    }

    /**
     * Allows to search gl accounts by filter.
     *
     * @param \App\Components\Finance\Models\Filters\GLAccountFilter $filter
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function filter(GLAccountFilter $filter): \Illuminate\Database\Query\Builder
    {
        $filterAsArray = $filter->toArray();

        $query = DB::table('gl_accounts_view');

        if (isset($filterAsArray['locations'])) {
            $query->whereIn('location_id', $filterAsArray['locations']);
            unset($filterAsArray['locations']);
        }

        $query->where($filterAsArray);

        return $query;
    }

    /**
     * Scope a query to only include gl accounts with a given code.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $code
     * @param int                                   $accountingOrganizationId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithCode(Builder $query, string $code, int $accountingOrganizationId): Builder
    {
        return $query->where([
            'code'                       => $code,
            'accounting_organization_id' => $accountingOrganizationId,
        ]);
    }

    /**
     * Scope a query with bank account.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int                                   $accountingOrganizationId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithBankAccount(Builder $query, int $accountingOrganizationId): Builder
    {
        return $query->where([
            'name'                       => self::TRADING_BANK_ACCOUNT_NAME,
            'accounting_organization_id' => $accountingOrganizationId,
        ]);
    }

    /**
     * Scope a query to only include gl accounts related to revenue type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $groupName   GL Account type group name.
     * @param int|null                              $glAccountId GL Account identifier.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAccountTypeGroupName(Builder $query, string $groupName, ?int $glAccountId = null): Builder
    {
        return $query
            ->whereHas('accountType', function (Builder $query) use ($groupName) {
                $query->whereHas('accountTypeGroup', function (Builder $query) use ($groupName) {
                    $query->where('name', '=', $groupName);
                });
            })
            ->when(null !== $glAccountId, function (Builder $query) use ($glAccountId) {
                return $query->where('id', $glAccountId);
            })
            ->orderBy('gl_accounts.name');
    }
}
