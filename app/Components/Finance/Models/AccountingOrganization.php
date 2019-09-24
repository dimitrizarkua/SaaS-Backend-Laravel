<?php

namespace App\Components\Finance\Models;

use App\Components\Contacts\Models\Contact;
use App\Components\Locations\Models\Location;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class AccountingOrganization
 *
 * @property int                             $id
 * @property int                             $contact_id
 * @property int|null                        $tax_payable_account_id
 * @property int|null                        $tax_receivable_account_id
 * @property int|null                        $accounts_payable_account_id
 * @property int|null                        $accounts_receivable_account_id
 * @property int|null                        $payment_details_account_id
 * @property string|null                     $cc_payments_api_key
 * @property bool                            $is_active
 * @property int|null                        $lock_day_of_month
 * @property-read Contact                    $contact
 * @property-read Collection|GLAccount[]     $glAccounts
 * @property-read Collection|Location[]      $locations
 * @property-read GLAccount|null             $payableAccount
 * @property-read GLAccount|null             $receivableAccount
 * @property-read GLAccount|null             $taxPayableAccount
 * @property-read GLAccount|null             $taxReceivableAccount
 * @property-read GLAccount|null             $paymentDetailsAccount
 * @property-read Collection|Transaction[]   $transactions
 * @property-read Collection|PurchaseOrder[] $purchaseOrders
 *
 * @method static Builder|AccountingOrganization newModelQuery()
 * @method static Builder|AccountingOrganization newQuery()
 * @method static Builder|AccountingOrganization query()
 * @method static Builder|AccountingOrganization whereAccountsPayableAccountId($value)
 * @method static Builder|AccountingOrganization whereAccountsReceivableAccountId($value)
 * @method static Builder|AccountingOrganization whereCcPaymentsApiKey($value)
 * @method static Builder|AccountingOrganization whereContactId($value)
 * @method static Builder|AccountingOrganization whereId($value)
 * @method static Builder|AccountingOrganization whereIsActive($value)
 * @method static Builder|AccountingOrganization whereTaxPayableAccountId($value)
 * @method static Builder|AccountingOrganization whereTaxReceivableAccountId($value)
 * @method static Builder|AccountingOrganization wherePaymentDetailsAccountId($value)
 * @method static Builder|AccountingOrganization lockDayOfMonthIsToday()
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={"id","contact_id","is_active"}
 * )
 */
class AccountingOrganization extends Model
{
    use ApiRequestFillable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="contact_id",
     *    description="Identifier of contact for which accounting organization is being creating",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="tax_payable_account_id",
     *    description="Identifier of Tax Payable GL Account",
     *    type="integer",
     *    nullable=true,
     *    example="1"
     * ),
     * @OA\Property(
     *    property="tax_receivable_account_id",
     *    description="Identifier of Tax Receivable GL Account",
     *    type="integer",
     *    nullable=true,
     *    example="1"
     * ),
     * @OA\Property(
     *    property="accounts_payable_account_id",
     *    description="Identifier of Accounts Payable GL Account",
     *    type="integer",
     *    nullable=true,
     *    example="1"
     * ),
     * @OA\Property(
     *    property="accounts_receivable_account_id",
     *    description="Identifier of Accounts Receivable GL Account",
     *    type="integer",
     *    nullable=true,
     *    example="1"
     * ),
     * @OA\Property(
     *    property="payment_details_account_id",
     *    description="Identifier of Payment details GL Account",
     *    type="integer",
     *    nullable=true,
     *    example="1"
     * ),
     * @OA\Property(
     *    property="cc_payments_api_key",
     *    description="Payments API key",
     *    type="string",
     *    nullable=true,
     *    example="API KEY"
     * ),
     * @OA\Property(
     *    property="is_active",
     *    description="Shows whether is this account active",
     *    type="boolean",
     * ),
     * @OA\Property(
     *    property="lock_day_of_month",
     *    description="End-of-month financial date",
     *    type="integer",
     *    example="14"
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
    public function taxPayableAccount(): BelongsTo
    {
        return $this->belongsTo(GLAccount::class, 'tax_payable_account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxReceivableAccount(): BelongsTo
    {
        return $this->belongsTo(GLAccount::class, 'tax_receivable_account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payableAccount(): BelongsTo
    {
        return $this->belongsTo(GLAccount::class, 'accounts_payable_account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receivableAccount(): BelongsTo
    {
        return $this->belongsTo(GLAccount::class, 'accounts_receivable_account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentDetailsAccount(): BelongsTo
    {
        return $this->belongsTo(GLAccount::class, 'payment_details_account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(
            Location::class,
            'accounting_organization_locations',
            'accounting_organization_id',
            'location_id'
        );
    }

    /**
     * Relationship with transactions table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'accounting_organization_id');
    }

    /**
     * Relationship with purchase orders table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'accounting_organization_id');
    }

    /**
     * Returns end-of-month financial date.
     *
     * @return Carbon
     */
    public function getEndOfMonthFinancialDate(): Carbon
    {
        //If lock date greater than current date then get date from previous month.
        $month = $this->lock_day_of_month > Carbon::now()->day ? 1 : 0;

        //If lock date greater than number of days in month then get last day of month.
        $day = min(
            Carbon::now()->subMonth($month)->daysInMonth,
            $this->lock_day_of_month
        );

        return Carbon::create(null, null, $day)
            ->subMonth($month);
    }

    /**
     * Scope a query to include accounting organizations which lock day of month is today.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLockDayOfMonthIsToday(Builder $query): Builder
    {
        return $query->where('lock_day_of_month', Carbon::now()->day);
    }


    /**
     * Indicates whether the entity's date is within the end-of-month financial date for specific AO.
     *
     * @return bool
     */
    public function isDateWithinCurrentFinancialMonth(Carbon $date): bool
    {
        return $date->gt($this->getEndOfMonthFinancialDate());
    }
}
