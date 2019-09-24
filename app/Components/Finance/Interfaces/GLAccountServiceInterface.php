<?php

namespace App\Components\Finance\Interfaces;

use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\VO\GLAccountTransactionFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Interface GLAccountServiceInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface GLAccountServiceInterface
{
    /**
     * Returns gl account by its id.
     *
     * @param int $glAccountId GL Account identifier.
     *
     * @return \App\Components\Finance\Models\GLAccount
     */
    public function getGLAccount(int $glAccountId): GLAccount;

    /**
     * Returns GL accounts by account type group name.
     *
     * @see \App\Components\Finance\Enums\AccountTypeGroups
     *
     * @param string   $groupName   Account type group name.
     * @param int|null $glAccountId GL Account identifier.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGLAccountsByGroupName(string $groupName, ?int $glAccountId = null): Collection;

    /**
     * Allows to find transaction records relevant for given GL account identifier.
     *
     * @param int                        $glAccountId GL Account identifier.
     * @param GLAccountTransactionFilter $filter      Options for filtering transactions.
     *
     * @return Builder
     */
    public function findTransactionRecordsByAccount(
        int $glAccountId,
        GLAccountTransactionFilter $filter = null
    ): Builder;

    /**
     * Gets all transactions before specified date and calculate balance on specified date.
     *
     * @param int                             $glAccountId GL Account identifier.
     * @param null|GLAccountTransactionFilter $filter      Options for filtering transactions.
     *
     * @return float Calculated balance value.
     */
    public function getAccountBalance(int $glAccountId, GLAccountTransactionFilter $filter = null): float;
}
