<?php

namespace App\Components\Finance\Services;

use App\Components\Finance\Domains\TransactionDomain;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\TransactionsServiceInterface;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class TransactionsService
 * This is a low-level transaction service, do not use it directly.
 *
 * @package App\Components\Finance\Services
 */
class TransactionsService implements TransactionsServiceInterface
{
    /**
     * @inheritdoc
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException in case if transaction doesn't exists.
     */
    public function getTransaction(int $transactionId): Transaction
    {
        return Transaction::findOrFail($transactionId);
    }

    /**
     * @inheritdoc
     */
    public function createTransaction(int $accountOrganizationId): TransactionDomain
    {
        return new TransactionDomain($accountOrganizationId);
    }

    /**
     * @inheritdoc
     *
     * @throws \App\Components\Finance\Exceptions\NotAllowedException in case if transaction is invalid.
     * @throws \Throwable
     */
    public function commitTransaction(TransactionDomain $transaction): int
    {
        if (false === $transaction->isValid()) {
            Log::debug('Unable to commit transaction', $transaction->toArray());
            throw new NotAllowedException('The transaction is not valid.');
        }

        $transactionId = null;
        DB::transaction(function () use ($transaction, &$transactionId) {
            $transactionModel = Transaction::create([
                'accounting_organization_id' => $transaction->getAccountingOrganizationId(),
            ]);
            $transactionModel->saveOrFail();
            $transactionId = $transactionModel->id;

            foreach ($transaction->getRecords() as $record) {
                $record->transaction_id = $transactionId;
                $record->saveOrFail();
            }
        });

        return $transactionId;
    }

    /**
     * @inheritdoc
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException in case if transaction doesn't exists.
     * @throws \App\Components\Finance\Exceptions\NotAllowedException in case if transaction is invalid.
     * @throws \Throwable
     */
    public function rollbackTransaction(int $transactionId): int
    {
        $transactionModel = $this->getTransaction($transactionId);

        $transaction = $this->createTransaction($transactionModel->accounting_organization_id);
        foreach ($transactionModel->records as $record) {
            $transaction->addRecord($record->gl_account_id, $record->amount, !$record->is_debit);
        }

        return $this->commitTransaction($transaction);
    }

    /**
     * @inheritdoc
     */
    public function addBalanceToTransactionRecords(
        AccountType $accountType,
        float $startBalance,
        Collection $records
    ): Collection {
        foreach ($records as $record) {
            /** @var TransactionRecord $record */
            $record->balance = $record->getBalance($accountType, $startBalance);
            $startBalance    = $record->balance;
        }

        return $records;
    }
}
