<?php

namespace App\Components\Finance\Interfaces;

use App\Components\Finance\Domains\TransactionDomain;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use Illuminate\Support\Collection;

/**
 * Interface TransactionsServiceInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface TransactionsServiceInterface
{
    /**
     * Returns transaction model by its id.
     *
     * @param int $transactionId Transaction id.
     *
     * @return \App\Components\Finance\Models\Transaction
     */
    public function getTransaction(int $transactionId): Transaction;

    /**
     * Creates new transaction domain model.
     *
     * @param int $accountOrganizationId Identifier of accounting organization.
     *
     * @return TransactionDomain
     */
    public function createTransaction(int $accountOrganizationId): TransactionDomain;

    /**
     * Commits the transaction.
     *
     * @param TransactionDomain $transaction Transaction domain model.
     *
     * @return int Identifier of the created transaction.
     */
    public function commitTransaction(TransactionDomain $transaction): int;

    /**
     * Rollback existing transaction by its id.
     *
     * @param int $transactionId Identifier of existing transaction.
     *
     * @return int Identifier of new transaction.
     */
    public function rollbackTransaction(int $transactionId): int;

    /**
     * Add calculated property `balance` for each transaction record based on start balance value.
     *
     * @param \App\Components\Finance\Models\AccountType         $accountType  Account type.
     * @param float                                              $startBalance Initial balance for calculation.
     * @param \Illuminate\Support\Collection|TransactionRecord[] $records      Transaction records with balance.
     *
     * @return \Illuminate\Support\Collection|TransactionRecord[] transaction records with balance.
     */
    public function addBalanceToTransactionRecords(
        AccountType $accountType,
        float $startBalance,
        Collection $records
    ): Collection;
}
