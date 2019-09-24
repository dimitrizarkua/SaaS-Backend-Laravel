<?php

namespace App\Components\Finance\Domains;

use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Models\TransactionRecord;
use App\Helpers\Decimal;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Log;

/**
 * Class TransactionDomain
 *
 * @package App\Components\Finance\Domains
 */
class TransactionDomain implements Arrayable
{
    /**
     * Identifier of accounting organization.
     *
     * @var int
     */
    private $accountingOrganizationId;
    /**
     * Transaction records list.
     *
     * @var TransactionRecord[]
     */
    private $records = [];

    /**
     * TransactionDomain constructor.
     *
     * @param int $accountingOrganizationId
     */
    public function __construct(int $accountingOrganizationId)
    {
        $this->accountingOrganizationId = $accountingOrganizationId;
    }

    /**
     * Allows to add new transaction record.
     *
     * @param int   $glAccountId Identifier of GL Account.
     * @param float $amount      Amount of the record.
     * @param bool  $isDebit     Is record debit.
     *
     * @return self
     * @throws NotAllowedException in case when $amount less or equals to zero.
     */
    public function addRecord(int $glAccountId, float $amount, bool $isDebit): self
    {
        if ($amount <= 0) {
            Log::debug(
                sprintf(
                    'Amount [AMOUNT:%s] must be greater than zero [GL_ACCOUNT_ID:%d], [IS_DEBIT:%d]',
                    $glAccountId,
                    $amount,
                    $isDebit
                )
            );
            throw new NotAllowedException('Amount must be greater than zero');
        }

        $this->records[] = new TransactionRecord([
            'gl_account_id' => $glAccountId,
            'amount'        => $amount,
            'is_debit'      => $isDebit,
        ]);

        return $this;
    }

    /**
     * Returns accounting organization id.
     *
     * @return int
     */
    public function getAccountingOrganizationId(): int
    {
        return $this->accountingOrganizationId;
    }

    /**
     * Returns all transaction records.
     *
     * @return TransactionRecord[]
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * Checks whether is transaction valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (0 === count($this->records)) {
            return false;
        }

        return Decimal::isZero($this->getTrialBalance());
    }

    /**
     * Returns trial balance of the transaction.
     *
     * @return float
     */
    public function getTrialBalance(): float
    {
        $trialBalance = 0;

        foreach ($this->records as $record) {
            if (true === $record->is_debit) {
                $trialBalance -= $record->amount;
            } else {
                $trialBalance += $record->amount;
            }
        }

        return (float)$trialBalance;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $records = [];
        foreach ($this->records as $record) {
            $records[] = [
                'amount'        => $record->amount,
                'gl_account_id' => $record->gl_account_id,
                'is_debit'      => $record->is_debit,
            ];
        }

        return [
            'accounting_organization_id' => $this->accountingOrganizationId,
            'records'                    => $records,
        ];
    }
}
