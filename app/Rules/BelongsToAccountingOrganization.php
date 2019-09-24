<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Components\Finance\Models\GLAccount as GLAccountModel;

/**
 * Class BelongsToAccountingOrganization
 *
 * @package App\Rules
 */
class BelongsToAccountingOrganization implements Rule
{
    /**
     * Accounting organization id for which GL Account is attaching.
     *
     * @var int
     */
    private $accountingOrganizationId;

    /**
     * BelongsToAccountingOrganization constructor.
     *
     * @param $accountingOrganizationId
     */
    public function __construct(int $accountingOrganizationId)
    {
        $this->accountingOrganizationId = $accountingOrganizationId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $glAccount = GLAccountModel::findOrFail($value);

        return $glAccount->accounting_organization_id === $this->accountingOrganizationId;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'The GL Account must belongs to an attached account organization';
    }
}
