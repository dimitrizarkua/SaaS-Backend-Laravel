<?php

namespace Tests\Unit\Finance\Domains;

use App\Components\Finance\Domains\TransactionDomain;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Models\TransactionRecord;
use Tests\TestCase;

/**
 * Class TransactionDomainTest
 *
 * @package Tests\Unit\Finance\Domains
 * @group   finance
 * @group   transaction-domain
 */
class TransactionDomainTest extends TestCase
{
    public function testAddRecordMethod()
    {
        $transaction = new TransactionDomain(0);
        $transaction->addRecord(1, 100, false)
            ->addRecord(1, 100, true);

        self::assertCount(2, $transaction->getRecords());
        foreach ($transaction->getRecords() as $record) {
            self::assertInstanceOf(TransactionRecord::class, $record);
        }
    }

    public function testExceptionShouldBeThrownWithNegativeAnount()
    {
        $transaction = new TransactionDomain(0);

        self::expectException(NotAllowedException::class);
        $transaction->addRecord(1, -1, false);
    }

    public function testExceptionShouldBeThrownWithZeroAmount()
    {
        $transaction = new TransactionDomain(0);

        self::expectException(NotAllowedException::class);
        $transaction->addRecord(1, 0, false);
    }

    public function testShouldReturnCorrectTrialBalance()
    {
        $transaction = new TransactionDomain(0);
        $transaction->addRecord(1, 100, false)
            ->addRecord(1, 100, true);

        self::assertEquals(0, $transaction->getTrialBalance());
    }

    public function testShouldReturnCorrectTrialBalanceWithFloatNumbers()
    {
        $transaction = new TransactionDomain(0);
        $transaction->addRecord(1, 100.33, false)
            ->addRecord(1, 100.33, true);

        self::assertEquals(0, $transaction->getTrialBalance());
    }

    public function testShouldReturnCorrectTrialBalanceWithEqualsRecords()
    {
        $transaction = new TransactionDomain(0);
        $transaction->addRecord(1, 100, true)
            ->addRecord(1, 100, true);

        self::assertEquals(-200, $transaction->getTrialBalance());
    }

    public function testTransactionShouldBeInvalidWithEmptyRecords()
    {
        $transaction = new TransactionDomain(0);

        self::assertFalse($transaction->isValid());
    }

    public function testTransactionShouldBeInvalidWithNonOddRecordsCount()
    {
        $transaction = new TransactionDomain(0);
        $transaction->addRecord(1, 100, true);

        self::assertFalse($transaction->isValid());
    }

    public function testTransactionShouldBeInvalidWithNonZeroTrialBalance()
    {
        $transaction = new TransactionDomain(0);
        $transaction->addRecord(1, 100, true)
            ->addRecord(1, 100, true);

        self::assertFalse($transaction->isValid());
    }

    public function testTransactionShouldBeValid()
    {
        $transaction = new TransactionDomain(0);
        $transaction->addRecord(1, 100, false)
            ->addRecord(1, 100, true)
            ->addRecord(1, 50, false)
            ->addRecord(1, 50, true);

        $result = $transaction->isValid();
        self::assertTrue($result);
    }

    public function testTransactionShouldBeValidOnSeveralFloatAmountCloseToZero()
    {
        $transaction = new TransactionDomain(0);
        $transaction
            ->addRecord(1, 0.099, false)
            ->addRecord(1, 0.088, true)
            ->addRecord(1, 0.010, true)
            ->addRecord(1, 0.001, true);

        $result = $transaction->isValid();
        self::assertTrue($result);
    }
}
