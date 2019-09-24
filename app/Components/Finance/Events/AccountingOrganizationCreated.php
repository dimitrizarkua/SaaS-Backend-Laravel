<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\AccountingOrganization;
use Illuminate\Queue\SerializesModels;

/**
 * Class AccountingOrganizationCreated
 *
 * @package App\Components\Finance\Events
 */
class AccountingOrganizationCreated
{
    use SerializesModels;

    /**
     * @var AccountingOrganization
     */
    public $accountingOrganization;

    /**
     * AccountingOrganizationCreated constructor.
     *
     * @param AccountingOrganization $accountingOrganization
     */
    public function __construct(AccountingOrganization $accountingOrganization)
    {
        $this->accountingOrganization = $accountingOrganization;
    }
}
