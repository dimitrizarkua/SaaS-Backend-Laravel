<?php

namespace App\Components\Finance\Interfaces;

use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\VO\CreateAccountingOrganizationData;

/**
 * Interface AccountingOrganizationsServiceInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface AccountingOrganizationsServiceInterface
{
    /**
     * Returns accounting organization model by its id.
     *
     * @param int $accountId Accounting organization id.
     *
     * @return \App\Components\Finance\Models\AccountingOrganization
     */
    public function getAccountingOrganization(int $accountId): AccountingOrganization;

    /**
     * Create Accounting Organization.
     *
     * @param CreateAccountingOrganizationData $data
     *
     * @return AccountingOrganization
     */
    public function create(CreateAccountingOrganizationData $data): AccountingOrganization;

    /**
     * Returns active accounting organization for given location.
     *
     * @param int $locationId Location id.
     *
     * @return AccountingOrganization|null
     */
    public function findActiveAccountOrganizationByLocation(int $locationId): ?AccountingOrganization;

    /**
     * Allows to link a location to an accounting organization.
     *
     * @param int $accountId  Accounting organization id.
     * @param int $locationId Location id.
     */
    public function addLocation(int $accountId, int $locationId): void;

    /**
     * Allows to unlink a location from an accounting organization.
     *
     * @param int $accountId  Accounting organization id.
     * @param int $locationId Location id.
     */
    public function removeLocation(int $accountId, int $locationId): void;
}
