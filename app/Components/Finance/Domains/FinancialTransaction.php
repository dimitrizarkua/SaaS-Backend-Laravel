<?php

namespace App\Components\Finance\Domains;

use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\TransactionsServiceInterface;
use App\Components\Finance\Models\GLAccount;

/**
 * Class FinancialTransaction
 * Allows to perform increase and decrease operations with GL Accounts
 * in the scope of one transaction.
 *
 * @package App\Components\Finance\Domains
 */
final class FinancialTransaction
{
    /**
     * Accounting organization id for which the transaction should be processed.
     *
     * @var int
     */
    private $accountingOrganizationId;

    /**
     * Current transaction.
     *
     * @var \App\Components\Finance\Domains\TransactionDomain
     */
    private $transaction;

    /**
     * Transaction service instance.
     *
     * @var TransactionsServiceInterface
     */
    private $transactionService;

    /**
     * A flag indicating whether the transaction has been processed.
     *
     * @var bool
     */
    private $processed = false;

    /**
     * AccountTransaction constructor.
     *
     * @param int $accountingOrganizationId Accounting organization id for which the transaction should be processed.
     */
    public function __construct(int $accountingOrganizationId)
    {
        $this->accountingOrganizationId = $accountingOrganizationId;

        $this->transaction = $this->getTransactionService()
            ->createTransaction($this->accountingOrganizationId);
    }

    /**
     * Convenient static factory of instance.
     *
     * @param int $accountingOrganizationId Accounting organization id for which the transaction should be processed.
     *
     * @return \App\Components\Finance\Domains\FinancialTransaction
     */
    public static function make(int $accountingOrganizationId): self
    {
        return new static($accountingOrganizationId);
    }

    /**
     * Commit current transaction.
     *
     * @return int Transaction id.
     */
    public function commit(): int
    {
        if (true === $this->processed) {
            throw new NotAllowedException('This transaction already has been processed.');
        }

        $transactionId = $this->getTransactionService()
            ->commitTransaction($this->transaction);

        $this->processed = true;

        return $transactionId;
    }

    /**
     * Increase the account balance for the given amount.
     *
     * @param GLAccount $account The account which balance should be increased.
     * @param float     $amount  The amount by which the balance should be increased.
     *
     * @return $this
     */
    public function increase(GLAccount $account, float $amount): self
    {
        $this->processOperation($account, $amount, true);

        return $this;
    }

    /**
     * Decrease the account balance for the given amount.
     *
     * @param GLAccount $account The account which balance should be decreased.
     * @param float     $amount  The amount by which the balance should be decreased.
     *
     * @return $this
     */
    public function decrease(GLAccount $account, float $amount): self
    {
        $this->processOperation($account, $amount, false);

        return $this;
    }

    /**
     * Returns TransactionServiceInterface instance.
     *
     * @return TransactionsServiceInterface
     */
    private function getTransactionService(): TransactionsServiceInterface
    {
        if (null === $this->transactionService) {
            $this->transactionService = app()->make(TransactionsServiceInterface::class);
        }

        return $this->transactionService;
    }

    /**
     * Process account operation.
     *
     * @param GLAccount $account             The account which balance should be decreased/increased.
     * @param float     $amount              The amount by which the balance should be decreased/increased.
     * @param bool      $isIncreaseOperation Shows whether is increase operation should've be processed.
     *
     * @throws NotAllowedException in case if GL Account belongs to another accounting organization.
     */
    private function processOperation(GLAccount $account, float $amount, bool $isIncreaseOperation): void
    {
        if ($account->accounting_organization_id !== $this->accountingOrganizationId) {
            throw new NotAllowedException(
                'The given GL Account doesn\'t belong to the accounting organization for 
            which transaction was created.'
            );
        }

        $isDebit = $this->determineIsDebitOperation($account, $isIncreaseOperation);

        $this->transaction->addRecord($account->id, $amount, $isDebit);
    }

    /**
     * Determines which operation should be used for the account (credit or debit).
     *
     * @param GLAccount $account             The account instance.
     * @param bool      $isIncreaseOperation Shows whether is increase operation is being processing.
     *
     * @return bool
     */
    private function determineIsDebitOperation(GLAccount $account, bool $isIncreaseOperation): bool
    {
        return $account->accountType->increase_action_is_debit === $isIncreaseOperation;
    }
}
