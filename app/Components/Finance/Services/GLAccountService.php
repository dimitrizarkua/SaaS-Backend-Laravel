<?php

namespace App\Components\Finance\Services;

use App\Components\Finance\Enums\AccountTypeGroups;
use App\Components\Finance\Interfaces\GLAccountServiceInterface;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\TransactionRecord;
use App\Components\Finance\Models\VO\GLAccountTransactionFilter;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Illuminate\Support\Collection;

/**
 * Class GLAccountService
 *
 * @package App\Components\Finance\Services
 */
class GLAccountService implements GLAccountServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getGLAccount(int $glAccountId): GLAccount
    {
        return GLAccount::findOrFail($glAccountId);
    }

    /**
     * @inheritDoc
     */
    public function getGLAccountsByGroupName(string $groupName, int $glAccountId = null): Collection
    {
        if (!in_array($groupName, AccountTypeGroups::values())) {
            throw new InvalidArgumentException(sprintf(
                'Invalid type %s specified, allowed values are: %s',
                $groupName,
                implode(',', AccountTypeGroups::values())
            ));
        }

        return GLAccount::byAccountTypeGroupName($groupName, $glAccountId)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function findTransactionRecordsByAccount(
        int $glAccountId,
        GLAccountTransactionFilter $filter = null
    ): Builder {
        //todo add transaction credit_note, after #
        $query = TransactionRecord::with(['transaction', 'transaction.payment']);
        if (null !== $filter) {
            $query->whereHas('transaction', function (Builder $query) use ($filter) {
                $query->when(
                    null !== $filter->getDateFrom(),
                    function (Builder $query) use ($filter) {
                        return $query->whereDate('created_at', '>=', $filter->getDateFrom());
                    }
                )->when(
                    null !== $filter->getDateTo(),
                    function (Builder $query) use ($filter) {
                        return $query->whereDate('created_at', '<=', $filter->getDateTo());
                    }
                );
            });
        }

        return $query->where('gl_account_id', '=', $glAccountId);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccountBalance(int $glAccountId, GLAccountTransactionFilter $filter = null): float
    {
        $glAccount = $this->getGLAccount($glAccountId);

        return (float)$this->findTransactionRecordsByAccount($glAccount->id, $filter)
            ->get()
            ->reduce(function ($balance, TransactionRecord $record) use ($glAccount) {
                return $record->getBalance($glAccount->accountType, $balance);
            }, 0);
    }
}
