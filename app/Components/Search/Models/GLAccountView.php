<?php

namespace App\Components\Search\Models;

use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\Filters\GLAccountFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use OpenApi\Annotations as OA;

/**
 * Class GLAccountView
 *
 * @property int    $id
 * @property string $name
 * @property string $code
 * @property int    $location_id
 * @property string $location_name
 * @property int    $account_type_id
 * @property string $account_type_name
 * @property bool   $is_debit
 * @property int    $accounting_organization_id
 * @property bool   $is_bank_account
 * @property bool   $enable_payments_to_account
 * @property int    $tax_rate_id
 * @property string $tax_rate_name
 * @property float  $tax_rate_value
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "name",
 *          "code",
 *          "location_id",
 *          "location_name",
 *          "account_type_id",
 *          "account_type_name",
 *          "is_debit",
 *          "accounting_organization_id",
 *          "is_bank_account",
 *          "enable_payments_to_account",
 *          "tax_rate_id",
 *          "tax_rate_name",
 *          "tax_rate_value",
 *     }
 * )
 */
class GLAccountView extends Model
{
    protected $table = 'gl_accounts_view';

    /**
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     description="GL account identifier.",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="GL account name.",
     *     example="Cash in Bank"
     * ),
     * @OA\Property(
     *     property="code",
     *     type="string",
     *     nullable=true,
     *     description="GL account code.",
     *     example="1010"
     * ),
     * @OA\Property(
     *     property="location_id",
     *     type="integer",
     *     description="Location identifier.",
     *     example=1
     * ),
     * @OA\Property(
     *     property="location_name",
     *     type="string",
     *     description="Location name.",
     *     example="Canberra"
     * ),
     * @OA\Property(
     *    property="account_type_id",
     *    type="integer",
     *    description="Account type identifier.",
     *    example=1
     *),
     * @OA\Property(
     *     property="account_type_name",
     *     type="string",
     *     description="Account type name.",
     *     example="Revenue - Other Income"
     * ),
     * @OA\Property(
     *     property="is_debit",
     *     type="boolean",
     *     description="Defines whether action if adding (increasing) money to this account results in debit operation
     * or not (otherwise credit)",
     *     example=true
     * ),
     * @OA\Property(
     *     property="accounting_organization_id",
     *     type="integer",
     *     description="Accounting organization identifier.",
     *     example=1
     * ),
     * @OA\Property(
     *     property="is_bank_account",
     *     type="boolean",
     *     description="Indicates whether bank account number was set",
     *     example=true
     * ),
     * @OA\Property(
     *     property="enable_payments_to_account",
     *     type="boolean",
     *     description="Indicates whether payments enabled for the account",
     *     example=true
     * ),
     * @OA\Property(
     *     property="tax_rate_id",
     *     type="integer",
     *     description="Tax rate identifier.",
     *     example=1
     * ),
     * @OA\Property(
     *     property="tax_rate_name",
     *     type="string",
     *     description="Tax rate name.",
     *     example="GST on Income"
     * ),
     * @OA\Property(
     *     property="tax_rate_value",
     *     type="number",
     *     format="float",
     *     example=0.1
     * )
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'tax_rate_value' => 'float',
    ];

    /**
     * Allows to search gl accounts by filter.
     *
     * @param \App\Components\Finance\Models\Filters\GLAccountFilter $filter
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function filter(GLAccountFilter $filter): Builder
    {
        $filterAsArray = $filter->toArray();

        $query = self::query();

        if (isset($filterAsArray['locations'])) {
            $query->whereIn('location_id', $filterAsArray['locations']);
            unset($filterAsArray['locations']);
        } else {
            if (!isset($filterAsArray['accounting_organization_id'])) {
                return $query->where($filterAsArray);
            }

            $accountingOrganization = AccountingOrganization::with('locations')
                ->find($filterAsArray['accounting_organization_id']);

            if (null === $accountingOrganization) {
                return $query->where($filterAsArray);
            }

            $accountingOrganizationLocations = $accountingOrganization->locations;

            if (null === $accountingOrganizationLocations) {
                return $query->where($filterAsArray);
            }

            $location = $accountingOrganizationLocations->first();
            $query->where('location_id', $location->id);
        }

        return $query->where($filterAsArray);
    }
}
