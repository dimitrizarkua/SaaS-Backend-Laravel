<?php

namespace Tests\Unit\Finance\Services;

use App\Components\Finance\Domains\TransactionDomain;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\TransactionsServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use Tests\TestCase;

/**
 * Class TransactionServiceTest
 *
 * @package Tests\Unit\Finance\Services
 * @group   finance
 */
class TransactionServiceTest extends TestCase
{
    /**
     * @var \App\Components\Finance\Interfaces\TransactionsServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();
        $this->service = $this->app->make(TransactionsServiceInterface::class);
        $this->models  = array_merge([
            TransactionRecord::class,
            Transaction::class,
            GLAccount::class,
            TaxRate::class,
            AccountType::class,
            AccountingOrganization::class,
        ], $this->models);
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->service);
    }

    public function testCreateMethodShouldReturnTransactionDomain()
    {
        $accountOrganizationId = $this->faker->randomDigit;
        $transaction           = $this->service->createTransaction($accountOrganizationId);

        self::assertInstanceOf(TransactionDomain::class, $transaction);
        self::assertEquals($accountOrganizationId, $transaction->getAccountingOrganizationId());
    }

    public function testCommitTransactionShouldSaveAllDataIntoDatabase()
    {
        $accountingOrganization = factory(AccountingOrganization::class)->create();
        $bankAccount            = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);
        $assetsAccount          = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);

        //Ensure that there is no saved transaction in the DB
        $result = Transaction::query()
            ->where('accounting_organization_id', $accountingOrganization->id)
            ->get();
        self::assertCount(0, $result);

        $amount      = $this->faker->randomFloat(2, 10, 1000);
        $transaction = $this->service->createTransaction($accountingOrganization->id)
            ->addRecord($bankAccount->id, $amount, true)
            ->addRecord($assetsAccount->id, $amount, false);

        $transactionId = $this->service->commitTransaction($transaction);

        //Get transaction from DB
        $results = Transaction::where('accounting_organization_id', $accountingOrganization->id)
            ->get();
        self::assertCount(1, $results);
        /** @var Transaction $existingTransaction */
        $existingTransaction = $results->first();
        self::assertCount(2, $existingTransaction->records);
        self::assertEquals($transactionId, $existingTransaction->id);

        $firstRecord = TransactionRecord::query()
            ->where('transaction_id', $existingTransaction->id)
            ->where('gl_account_id', $bankAccount->id)
            ->first();
        self::assertNotNull($firstRecord);
        self::assertEquals($amount, $firstRecord->amount);
        self::assertTrue($firstRecord->is_debit);

        $secondRecord = TransactionRecord::where('transaction_id', $existingTransaction->id)
            ->where('gl_account_id', $assetsAccount->id)
            ->first();
        self::assertNotNull($secondRecord);
        self::assertEquals($amount, $secondRecord->amount);
        self::assertFalse($secondRecord->is_debit);
    }

    public function testNotAllowedExceptionShouldBeThrownInCaseOfInvalidTransaction()
    {
        $transaction = $this->service->createTransaction(0)
            ->addRecord(1, 100, true)
            ->addRecord(2, 101, false);

        self::expectException(NotAllowedException::class);
        $this->service->commitTransaction($transaction);
    }

    public function testRollbackOperationShouldCreateNewTransactionAndRecords()
    {
        $accountingOrganization = factory(AccountingOrganization::class)->create();
        $bankAccount            = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);
        $assetsAccount          = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);

        /** @var Transaction $transaction */
        $transaction = factory(Transaction::class)->create();
        $amount      = $this->faker->randomFloat(2, 10, 1000);
        /** @var TransactionRecord $firstRecordOfOldTransaction */
        $firstRecordOfOldTransaction = factory(TransactionRecord::class)->create([
            'transaction_id' => $transaction->id,
            'gl_account_id'  => $bankAccount->id,
            'amount'         => $amount,
            'is_debit'       => false,
        ]);
        /** @var TransactionRecord $secondRecordOfOldTransaction */
        $secondRecordOfOldTransaction = factory(TransactionRecord::class)->create([
            'transaction_id' => $transaction->id,
            'gl_account_id'  => $assetsAccount->id,
            'amount'         => $amount,
            'is_debit'       => true,
        ]);

        $newTransactionId = $this->service->rollbackTransaction($transaction->id);

        $model = Transaction::find($newTransactionId);
        self::assertNotNull($model);
        self::assertCount(2, $model->records);

        $firstRecordOfNewTransaction = TransactionRecord::where('transaction_id', $newTransactionId)
            ->where('gl_account_id', $bankAccount->id)
            ->first();
        self::assertNotNull($firstRecordOfNewTransaction);
        self::assertEquals($firstRecordOfOldTransaction->amount, $firstRecordOfNewTransaction->amount);
        self::assertEquals($firstRecordOfOldTransaction->is_debit, !$firstRecordOfNewTransaction->is_debit);

        $secondRecordOfNewTransaction = TransactionRecord::where('transaction_id', $newTransactionId)
            ->where('gl_account_id', $assetsAccount->id)
            ->first();
        self::assertNotNull($secondRecordOfNewTransaction);
        self::assertEquals($secondRecordOfOldTransaction->amount, $secondRecordOfNewTransaction->amount);
        self::assertEquals($secondRecordOfOldTransaction->is_debit, !$secondRecordOfNewTransaction->is_debit);
    }
}
