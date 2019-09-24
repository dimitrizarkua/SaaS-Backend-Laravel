<?php

namespace App\Components\Finance\Models\Filters;

use App\Core\JsonModel;

/**
 * Class GLAccountFilter
 * Defines search filter options for gl accounts. All public properties map to database fields.
 *
 * @package App\Components\Finance\Models\Filters
 */
class GLAccountFilter extends JsonModel
{
    /** @var int|null */
    public $gl_account_id;

    /** @var array|null */
    public $locations;

    /** @var int|null */
    public $accounting_organization_id;

    /** @var int|null */
    public $account_type_id;

    /** @var boolean|null returns accounts where bank_account_number was set */
    public $is_bank_account;

    /** @var boolean|null returns accounts with enabled payments */
    public $enable_payments_to_account;

    /** @var bool|null */
    public $is_debit;

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter(parent::toArray(), function ($value) {
            return null !== $value;
        });
    }
}
